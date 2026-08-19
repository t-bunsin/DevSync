<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
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
        ];
    }
}
