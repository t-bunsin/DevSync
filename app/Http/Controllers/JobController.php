<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
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

    private function catalog(): Collection
    {
        return collect(config('jobs_demo', []));
    }

    private function explorerData(): array
    {
        $jobs = $this->catalog();
        $selectedJob = $jobs->firstWhere('highlighted', true) ?? $jobs->first();

        abort_unless($selectedJob, 404);

        return [
            'jobs' => $jobs->values()->all(),
            'selectedJob' => $selectedJob,
        ];
    }
}
