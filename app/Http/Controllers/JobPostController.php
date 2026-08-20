<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Compliance;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $posts = JobPost::query()
            // The count travels with the employer so every row resolves its
            // verification badge without a query per company. Same idiom as
            // JobController::catalog().
            ->with(['author', 'employer' => fn ($query) => $query->withCount([
                'complianceRecords as verified_compliance_count' => fn ($records) => $records
                    ->where('status', Compliance::STATUS_VERIFIED),
            ])])
            // One grouped count for the whole page rather than a query per row,
            // which is what fills the Applications column.
            ->withCount('applications')
            ->search($request->query('q'))
            ->postedBetween($from, $to)
            ->when(
                in_array($request->query('status'), JobPost::statuses(), true),
                fn ($query) => $query->where('status', $request->query('status'))
            )
            ->orderByDesc('created_at')
            ->get();

        // Off the whole table, so the tiles keep their meaning under a filter.
        $counts = JobPost::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('job-posts.index', [
            'posts' => $posts,
            'counts' => $counts,
            'activeStatus' => $request->query('status'),
            'searchTerm' => $request->query('q'),
            'fromDate' => $from,
            'toDate' => $to,
        ]);
    }

    public function create()
    {
        return view('job-posts.create', [
            'post' => new JobPost(),
            'companies' => Company::approved()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $post = new JobPost($validated);
        $post->company = Company::findOrFail($validated['company_id'])->name;
        $post->created_by = $request->user()?->getKey();

        $post->slug = JobPost::makeSlug($request->input('slug') ?: $request->input('title'));
        $post->tabs = $this->tabsFrom($request);
        $this->syncPublishing($post);

        $post->save();
        $this->enforceSingleHighlight($post);

        return redirect()
            ->route('job-posts.index')
            ->withSuccess("“{$post->title}” was created.");
    }

    public function show(JobPost $jobPost)
    {
        $jobPost->load(['employer', 'author'])->loadCount('applications');

        return view('job-posts.show', ['post' => $jobPost]);
    }

    public function edit(JobPost $jobPost)
    {
        return view('job-posts.edit', [
            'post' => $jobPost,
            'companies' => Company::approved()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, JobPost $jobPost)
    {
        $validated = $this->validated($request);

        $jobPost->fill($validated);
        $jobPost->company = Company::findOrFail($validated['company_id'])->name;

        $jobPost->slug = JobPost::makeSlug(
            $request->input('slug') ?: $request->input('title'),
            $jobPost->id
        );
        $jobPost->tabs = $this->tabsFrom($request);
        $this->syncPublishing($jobPost);

        $jobPost->save();
        $this->enforceSingleHighlight($jobPost);

        return redirect()
            ->route('job-posts.index')
            ->withSuccess("“{$jobPost->title}” was updated.");
    }

    public function destroy(JobPost $jobPost)
    {
        $title = $jobPost->title;
        $jobPost->delete();

        return redirect()
            ->route('job-posts.index')
            ->withSuccess("“{$title}” was deleted.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'location' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
            'salary' => 'nullable|string|max:255',
            'short_salary' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in(JobPost::types())],
            'mode' => ['required', Rule::in(JobPost::modes())],
            'experience' => 'nullable|string|max:60',
            'department' => 'nullable|string|max:80',
            'deadline' => 'nullable|date',
            'applicants' => 'nullable|integer|min:0|max:1000000',
            'logo' => ['required', Rule::in(JobPost::logos())],
            'status' => ['required', Rule::in(JobPost::statuses())],
            'featured' => 'nullable|boolean',
            'highlighted' => 'nullable|boolean',
            'quick_apply_title' => 'nullable|string|max:255',
            'quick_apply_text' => 'nullable|string|max:500',
            'benefits' => 'nullable|string|max:2000',
            'highlights' => 'nullable|string|max:2000',
            'career_opportunities' => 'nullable|string|max:2000',
        ], [
            'company_id.required' => 'Choose which company is hiring.',
            'slug.regex' => 'The URL slug may only use lowercase letters, numbers and hyphens.',
        ]);
    }

    /**
     * The three detail panels arrive as tabs[key][field]; the bullet lists are
     * a textarea, one item per line.
     */
    private function tabsFrom(Request $request): array
    {
        $input = $request->input('tabs', []);
        $tabs = [];

        foreach (JobPost::TABS as $key) {
            $panel = $input[$key] ?? [];

            $tabs[$key] = [
                'title' => trim((string) ($panel['title'] ?? '')) ?: null,
                'body' => trim((string) ($panel['body'] ?? '')) ?: null,
                'list_title' => trim((string) ($panel['list_title'] ?? '')) ?: null,
                'list' => $this->lines($panel['list'] ?? ''),
            ];

            $tabs[$key] = array_filter(
                $tabs[$key],
                fn ($value) => $value !== null && $value !== []
            );
        }

        return array_filter($tabs, fn (array $panel) => $panel !== []);
    }

    private function lines(?string $value): array
    {
        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $value) ?: []
        ), fn (string $line) => $line !== ''));
    }

    /** Publishing stamps the date once; unpublishing keeps history intact. */
    private function syncPublishing(JobPost $post): void
    {
        if ($post->isPublished() && $post->published_at === null) {
            $post->published_at = now();
        }
    }

    /**
     * The explorer opens on the highlighted role, so exactly one post may hold
     * that flag. Setting it here clears it everywhere else.
     */
    private function enforceSingleHighlight(JobPost $post): void
    {
        if (! $post->highlighted) {
            return;
        }

        JobPost::query()
            ->whereKeyNot($post->id)
            ->where('highlighted', true)
            ->update(['highlighted' => false]);
    }
}
