<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Compliance;
use App\Models\JobPost;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Rows per page on the job posts list, matching the applications list. */
    private const PER_PAGE = 5;

    public function index(Request $request)
    {
        [$query, $from, $to] = $this->filteredQuery($request);

        $posts = $query
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // Off the whole table, so the tiles keep their meaning under a filter
        // — but still an employer's own posts only, or the tiles would count
        // every other employer's rows too.
        $counts = JobPost::query()
            ->when(! $request->user()->isAdmin(), fn (Builder $q) => $q->where('created_by', $request->user()->id))
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

    /**
     * Every row matching the current filters, laid out one per line for a
     * spreadsheet instead of a page — same query as the index, without the
     * paging so the export always covers the whole filtered set.
     */
    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->hasPermission(Permission::JOB_DOWNLOAD), 403);

        [$query] = $this->filteredQuery($request);

        $posts = $query->orderByDesc('created_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Job posts');

        $headings = ['Title', 'Company', 'Location', 'Type', 'Mode', 'Status', 'Applications', 'Registered', 'Post by'];
        $sheet->fromArray($headings, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);

        $row = 2;
        foreach ($posts as $post) {
            // Strict null comparison: fromArray()'s default `!= $nullValue`
            // check is loose, so an application count of 0 reads as "equal to
            // null" and silently drops the cell.
            $sheet->fromArray([
                $post->title,
                $post->company,
                $post->location,
                $post->type,
                $post->mode,
                ucfirst($post->status),
                $post->applications_count,
                optional($post->created_at)->format('Y-m-d H:i'),
                $post->author?->email ?: 'Unknown',
            ], null, 'A' . $row, true);
            $row++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'job-posts-' . now()->format('Y-m-d-His') . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * The search/status/date filters shared by the index page and the export,
     * so the spreadsheet always matches what's on screen. Returns the bounds
     * alongside the query since the index view echoes them back into the
     * filter inputs.
     */
    private function filteredQuery(Request $request): array
    {
        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $query = JobPost::query()
            // The count travels with the employer so every row resolves its
            // verification badge without a query per company. Same idiom as
            // JobController::catalog().
            ->with(['author', 'employer' => fn ($q) => $q->withCount([
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
                fn (Builder $q) => $q->where('status', $request->query('status'))
            )
            // Employer sees only their own posts; admin sees everything.
            ->when(
                ! $request->user()->isAdmin(),
                fn (Builder $q) => $q->where('created_by', $request->user()->id)
            );

        return [$query, $from, $to];
    }

    public function create()
    {
        abort_unless(request()->user()->hasPermission(Permission::JOB_CREATE), 403);

        return view('job-posts.create', [
            'post' => new JobPost(),
            'companies' => $this->selectableCompanies(request()->user()),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasPermission(Permission::JOB_CREATE), 403);

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
        $this->authorizeOwner($jobPost);

        $jobPost->load(['employer', 'author'])->loadCount('applications');

        return view('job-posts.show', ['post' => $jobPost]);
    }

    public function edit(JobPost $jobPost)
    {
        $this->authorizeOwner($jobPost, Permission::JOB_EDIT);

        return view('job-posts.edit', [
            'post' => $jobPost,
            'companies' => $this->selectableCompanies(request()->user()),
        ]);
    }

    public function update(Request $request, JobPost $jobPost)
    {
        $this->authorizeOwner($jobPost, Permission::JOB_EDIT);

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
        $this->authorizeOwner($jobPost, Permission::JOB_DELETE);

        $title = $jobPost->title;
        $jobPost->delete();

        return redirect()
            ->route('job-posts.index')
            ->withSuccess("“{$title}” was deleted.");
    }

    /**
     * Admin may touch any post; everyone else only the one they created — and,
     * when $permission is given, only if their role has also been granted it
     * (see the Roles page's job-post permission matrix).
     */
    private function authorizeOwner(JobPost $jobPost, ?string $permission = null): void
    {
        $user = request()->user();

        abort_unless($user->isAdmin() || $jobPost->created_by === $user->id, 403);
        abort_if($permission && ! $user->hasPermission($permission), 403);
    }

    /**
     * Admin picks from the whole approved directory; an employer may only
     * hire under their own company (see User::ownCompany()), so that's the
     * one option they get — or none, if their company isn't approved yet.
     */
    private function selectableCompanies($user)
    {
        if ($user->isAdmin()) {
            return Company::approved()->orderBy('name')->get();
        }

        $own = $user->ownCompany();

        return ($own && $own->isApproved()) ? collect([$own]) : collect();
    }

    /** Blocks a non-admin from posting under a company that isn't their own, even by editing the request directly. */
    private function authorizeCompany($user, string $companyId): void
    {
        abort_unless($user->isAdmin() || $user->ownCompany()?->id === (int) $companyId, 403);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'location' => ['required', 'string', 'max:255', Rule::in(JobPost::locationOptions())],
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/',
            'salary' => 'nullable|string|max:255',
            'short_salary' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:1000',
            'type' => ['required', Rule::in(JobPost::types())],
            'mode' => ['required', Rule::in(JobPost::modes())],
            'experience' => 'nullable|string|max:60',
            'department' => ['nullable', 'string', 'max:80', Rule::in(JobPost::departmentOptions())],
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
            'location.in' => 'Choose a location from the list.',
            'department.in' => 'Choose a department from the list.',
        ]);

        $this->authorizeCompany($request->user(), $validated['company_id']);

        // Featured placement is an admin call (see FeaturedJobController) —
        // strip it here too, since the form only hides the checkbox from
        // everyone else rather than actually stopping a raw POST.
        if (! $request->user()->isAdmin()) {
            unset($validated['featured']);
        }

        return $validated;
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
