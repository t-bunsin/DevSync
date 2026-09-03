<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Compliance;
use App\Models\JobPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * The public employer directory: /companies and /companies/{slug}.
 *
 * Deliberately separate from CompaniesController, which is the signed-in
 * register behind /admin — that one is scoped by role and shows pending and
 * rejected records, this one only ever serves approved employers to visitors.
 */
class CompanyDirectoryController extends Controller
{
    /** Companies per page in the directory grid. */
    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q'));
        $industry = $this->industryFilter($request);

        // Same guard the public job pages carry: these routes are open to
        // everyone and must keep rendering before the module is migrated.
        $companies = Schema::hasTable('companies')
            ? $this->baseQuery()
                ->search($term)
                ->when($industry, fn (Builder $query) => $query->where('industry', $industry))
                // Employers who are actually hiring lead the directory; the
                // rest fall back to alphabetical so the order is stable.
                ->orderByDesc('open_jobs_count')
                ->orderBy('name')
                ->paginate(self::PER_PAGE)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, self::PER_PAGE);

        return view('companies.index', [
            'companies' => $companies,
            'industries' => $this->industriesInUse(),
            'activeIndustry' => $industry,
            'searchTerm' => $term,
            'totalCompanies' => $this->totalApproved(),
            'totalOpenRoles' => $this->totalOpenRoles(),
        ]);
    }

    public function show(string $company): View
    {
        abort_unless(Schema::hasTable('companies'), 404);

        $record = $this->baseQuery()->where('slug', $company)->first();

        abort_unless($record, 404);

        return view('companies.show', [
            'company' => $record,
            'jobs' => $this->publishedJobs($record),
            'relatedCompanies' => $this->relatedCompanies($record),
        ]);
    }

    /**
     * Approved employers only, carrying the two counts every card reads: open
     * roles, and the verified licences behind the badge. Counting here keeps a
     * page of cards at one query instead of two per company — Company::
     * hasVerifiedCompliance() picks the alias up by name.
     */
    private function baseQuery(): Builder
    {
        return Company::query()
            ->approved()
            ->withCount([
                'jobPosts as open_jobs_count' => fn ($query) => $query
                    ->where('status', JobPost::STATUS_PUBLISHED),
                'complianceRecords as verified_compliance_count' => fn ($query) => $query
                    ->where('status', Compliance::STATUS_VERIFIED),
            ]);
    }

    /**
     * The employer's live adverts, mapped into the same array shape the job
     * cards elsewhere read. The company is handed to each post up front so the
     * mapping does not re-query the employer once per row.
     */
    private function publishedJobs(Company $company): array
    {
        if (! Schema::hasTable('job_posts')) {
            return [];
        }

        return $company->jobPosts()
            ->where('status', JobPost::STATUS_PUBLISHED)
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->get()
            ->map(function (JobPost $post) use ($company) {
                $post->setRelation('employer', $company);

                return $post->toCatalogArray();
            })
            ->all();
    }

    /**
     * A short "employers like this one" rail: same industry first, and anyone
     * else hiring when the industry is empty or unset, so the profile never
     * ends on a dead end.
     */
    private function relatedCompanies(Company $company): array
    {
        return $this->baseQuery()
            ->whereKeyNot($company->getKey())
            ->when(
                filled($company->industry),
                fn (Builder $query) => $query->orderByRaw('CASE WHEN industry = ? THEN 0 ELSE 1 END', [$company->industry])
            )
            ->orderByDesc('open_jobs_count')
            ->orderBy('name')
            ->take(3)
            ->get()
            ->all();
    }

    /** Only the industries an approved employer actually sits in. */
    private function industriesInUse(): array
    {
        if (! Schema::hasTable('companies')) {
            return [];
        }

        return Company::query()
            ->approved()
            ->whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
            ->all();
    }

    private function industryFilter(Request $request): ?string
    {
        $industry = (string) $request->query('industry');

        return in_array($industry, $this->industriesInUse(), true) ? $industry : null;
    }

    private function totalApproved(): int
    {
        return Schema::hasTable('companies') ? Company::query()->approved()->count() : 0;
    }

    private function totalOpenRoles(): int
    {
        if (! Schema::hasTable('companies') || ! Schema::hasTable('job_posts')) {
            return 0;
        }

        return JobPost::query()
            ->where('status', JobPost::STATUS_PUBLISHED)
            ->whereHas('employer', fn (Builder $query) => $query->approved())
            ->count();
    }
}
