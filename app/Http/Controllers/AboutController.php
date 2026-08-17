<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Compliance;
use App\Models\JobPost;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        return view('about', [
            'stats' => $this->stats(),
            'employers' => $this->employers(),
        ]);
    }

    /**
     * The employers actually on the platform, busiest first. Both counts are
     * eager-loaded: `verified_compliance_count` is the attribute
     * Company::hasVerifiedCompliance() reads before it falls back to a query.
     */
    private function employers()
    {
        if (! Schema::hasTable('companies') || ! Schema::hasTable('job_posts')) {
            return collect();
        }

        return Company::approved()
            ->withCount([
                'jobPosts as open_jobs_count' => fn ($query) => $query->where('status', JobPost::STATUS_PUBLISHED),
                'complianceRecords as verified_compliance_count' => fn ($query) => $query
                    ->where('status', Compliance::STATUS_VERIFIED),
            ])
            ->orderByDesc('open_jobs_count')
            ->orderBy('name')
            ->take(6)
            ->get();
    }

    /**
     * The stats band reads live numbers instead of hard-coded claims, so the
     * page cannot drift from what the platform actually holds. Guarded with
     * hasTable the way JobController::catalog() is: a fresh install without
     * the module tables still renders the page, every figure at zero.
     */
    private function stats(): array
    {
        $hasJobs = Schema::hasTable('job_posts');
        $hasCompanies = Schema::hasTable('companies');
        $hasCompliance = Schema::hasTable('compliances');

        return [
            'open_jobs' => $hasJobs ? JobPost::published()->count() : 0,
            'employers' => $hasCompanies ? Company::approved()->count() : 0,
            'verified' => $hasCompanies && $hasCompliance
                ? Company::approved()
                    ->whereHas('complianceRecords', fn ($query) => $query->where('status', Compliance::STATUS_VERIFIED))
                    ->count()
                : 0,
            'applicants' => $hasJobs ? (int) JobPost::published()->sum('applicants') : 0,
        ];
    }
}
