<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Models\Resume;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewJobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class JobController extends Controller
{
    public function landing(): View
    {
        return view('main', $this->explorerData());
    }

    public function index(): View
    {
        return view('jobs.index', $this->explorerData());
    }

    public function show(string $job): View
    {
        $jobs = $this->catalog();
        $selectedJob = $jobs->firstWhere('id', $job);

        abort_unless($selectedJob, 404);

        $relatedJobs = $jobs
            ->reject(fn (array $item) => $item['id'] === $selectedJob['id'])
            ->sortByDesc(fn (array $item) => (int) $item['featured'])
            ->take(3)
            ->values()
            ->all();

        return view('jobs.show', [
            'job' => $selectedJob,
            'relatedJobs' => $relatedJobs,
            'ownResume' => $this->ownResume(Auth::user()),
        ]);
    }

    /**
     * The apply gate. Browsing stays open to everyone; sending an application
     * needs an account, so a guest is parked at registration and returned to
     * this job — with the form open — as soon as the account exists.
     */
    public function apply(string $job): RedirectResponse
    {
        $selectedJob = $this->catalog()->firstWhere('id', $job);

        abort_unless($selectedJob, 404);

        $target = route('jobs.show', $selectedJob['id']) . '?apply=1';

        if (Auth::check()) {
            return redirect()->to($target);
        }

        // Both the login and register controllers finish on this key, so the
        // visitor lands back here whichever route they take.
        session(['url.intended' => $target]);

        return redirect()->route('register')->with('status', sprintf(
            'Create a free account to apply for %s at %s.',
            $selectedJob['title'],
            $selectedJob['company'],
        ));
    }

    /**
     * Records the application. This is what makes the Applications count in
     * the back office mean something — before this, the dialog only drew a
     * success message and nothing was ever stored.
     *
     * firstOrNew, not create: the unique index already forbids a second row
     * for the same account and post, and re-applying should read as the
     * candidate correcting their details rather than as an error.
     */
    public function storeApplication(Request $request, string $job): JsonResponse|RedirectResponse
    {
        $post = JobPost::query()->published()->where('slug', $job)->first();

        abort_unless($post, 404);

        $user = $request->user();
        $resumeId = $this->resumeIdFor($user);

        /*
         * A CV is required, by either route: the candidate uploads a file here,
         * or they built a resume in the dashboard and it travels with the
         * application. Only the first is asked for when the second is missing.
         */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'message' => 'nullable|string|max:2000',
            'cv' => [
                $resumeId ? 'nullable' : 'required',
                'file',
                'mimes:' . implode(',', JobApplication::CV_MIMES),
                'max:' . JobApplication::CV_MAX_KB,
            ],
        ], [
            'cv.required' => 'Attach your CV to apply, or build one in your dashboard first.',
            'cv.mimes' => 'Upload your CV as a PDF or a Word document.',
            'cv.max' => 'The CV must be 5 MB or smaller.',
        ]);

        $application = JobApplication::firstOrNew([
            'job_post_id' => $post->id,
            'user_id' => $user->getKey(),
        ]);

        $application->fill([
            'full_name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        // Set rather than filled: which post, which account and which CV are
        // decided here, never by the submitted form, so they stay off the
        // model's fillable list. Same call as Resume's `created_by`.
        $application->job_post_id = $post->id;
        $application->user_id = $user->getKey();
        $application->resume_id = $resumeId;
        $application->applied_at = now();

        $this->storeCv($application, $request);

        $application->save();

        if ($application->wasRecentlyCreated) {
            $this->announce($application, $post);
        }

        $message = sprintf('Your application for %s at %s was sent.', $post->title, $post->company);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'applicants' => $post->applications()->count(),
            ], $application->wasRecentlyCreated ? 201 : 200);
        }

        return redirect()
            ->route('jobs.show', $post->slug)
            ->with('status', $message);
    }

    /**
     * Tells the back office a candidate applied.
     *
     * Only on a first application: re-applying edits details the employer has
     * already been told about, and ringing the bell again for it is noise.
     *
     * Goes to whoever registered the post, and to the administrators — a post
     * with no author (the ones that predate `created_by`) would otherwise
     * notify nobody at all.
     */
    private function announce(JobApplication $application, JobPost $post): void
    {
        $recipients = User::query()
            ->whereHas('roles', fn ($query) => $query->where('code', Role::ADMIN))
            ->when($post->created_by, fn ($query) => $query->orWhere('id', $post->created_by))
            ->whereNull('deleted_at')
            ->get();

        // Loaded so the notification's copy can name the role without a query
        // per recipient.
        $application->setRelation('jobPost', $post);

        foreach ($recipients as $recipient) {
            $recipient->notify(new NewJobApplication($application));
        }
    }

    /**
     * Files the uploaded CV, replacing the one a previous application to this
     * post carried so re-applying does not leave orphans on disk.
     *
     * The `local` disk, not `public`: see the migration that added the column.
     */
    private function storeCv(JobApplication $application, Request $request): void
    {
        if (! $request->hasFile('cv')) {
            return;
        }

        $application->deleteCvFile();

        $file = $request->file('cv');
        $application->cv_path = $file->store('job-applications/' . $application->job_post_id, 'local');
        $application->cv_name = $file->getClientOriginalName();
    }

    /**
     * The CV the candidate already keeps on the platform, attached so the
     * employer can open it from the application. Null when they have none —
     * which is what makes the upload field on the apply form required.
     */
    private function ownResume($user): ?Resume
    {
        // The hasTable() guard is the same one catalog() carries: these pages
        // are public and must keep rendering before this module has been
        // migrated. Without a resumes table nobody has one on file, so the
        // apply form asks for an upload — which is the right answer anyway.
        if (! $user || ! Schema::hasTable('resumes')) {
            return null;
        }

        return Resume::query()
            ->where('created_by', $user->getKey())
            // Portable ordering: MySQL's FIELD() does not exist on the sqlite
            // connection the test suite runs against.
            ->orderByRaw("CASE status WHEN 'published' THEN 0 WHEN 'draft' THEN 1 ELSE 2 END")
            ->orderByDesc('updated_at')
            ->first();
    }

    private function resumeIdFor($user): ?int
    {
        return $this->ownResume($user)?->id;
    }

    /**
     * Published job posts, mapped into the array shape the job views have
     * always read. Falls back to config/jobs_demo.php when there is nothing to
     * show, so a fresh install still renders a populated site instead of an
     * empty explorer.
     *
     * The hasTable() guard is deliberate: these pages are public and must keep
     * rendering before this module has been migrated.
     */
    private function catalog(): Collection
    {
        if (! Schema::hasTable('job_posts')) {
            return collect(config('jobs_demo', []));
        }

        $posts = JobPost::query()
            // The count travels with the employer so the whole listing resolves
            // its verification badges without a query per company.
            ->with(['employer' => fn ($query) => $query->withCount([
                'complianceRecords as verified_compliance_count' => fn ($records) => $records
                    ->where('status', Compliance::STATUS_VERIFIED),
            ])])
            ->published()
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->get()
            ->map(fn (JobPost $post) => $post->toCatalogArray());

        return $posts->isNotEmpty() ? $posts : collect(config('jobs_demo', []));
    }

    /**
     * The three roles shown in the hero slideshow: featured first, then the
     * most recently posted.
     */
    private function spotlightJobs(Collection $jobs): array
    {
        return $jobs
            ->sortBy([
                fn (array $a, array $b) => (int) $b['featured'] <=> (int) $a['featured'],
                fn (array $a, array $b) => $a['posted_days'] <=> $b['posted_days'],
            ])
            ->take(3)
            ->values()
            ->all();
    }

    private function explorerData(): array
    {
        $jobs = $this->catalog();
        $selectedJob = $jobs->firstWhere('highlighted', true) ?? $jobs->first();

        abort_unless($selectedJob, 404);

        return [
            'jobs' => $jobs->values()->all(),
            'selectedJob' => $selectedJob,
            'spotlightJobs' => $this->spotlightJobs($jobs),
            'ownResume' => $this->ownResume(Auth::user()),
        ];
    }
}
