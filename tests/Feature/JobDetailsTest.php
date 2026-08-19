<?php

namespace Tests\Feature;

use Tests\TestCase;

class JobDetailsTest extends TestCase
{
    public function test_jobs_index_is_public_and_renders_one_complete_explorer(): void
    {
        $response = $this->get(route('jobs.index'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, 'data-jobs-explorer'));
        $response->assertDontSee('jf-job-ticker', false);

        $requiredIds = [
            'job-search',
            'job-search-input',
            'job-location-input',
            'job-category-select',
            'job-mode-select',
            'job-search-button',
            'jf-reset-search',
            'jobs',
            'job-count',
            'job-sort-select',
            'job-card-list',
            'jf-no-results',
            'detail-badge',
            'detail-save-button',
            'detail-title',
            'detail-company',
            'detail-location',
            'detail-posted',
            'detail-salary',
            'detail-applicants',
            'detail-apply-button',
            'detail-page-link',
            'detail-panel-content',
            'detail-section-title',
            'detail-section-body',
            'detail-list-title',
            'detail-list',
            'detail-facts',
            'detail-quick-apply',
            'detail-quick-title',
            'detail-quick-text',
            'jobs-data',
        ];

        foreach ($requiredIds as $id) {
            $this->assertSame(1, substr_count($html, 'id="' . $id . '"'), "Expected #{$id} exactly once.");
        }

        // The application form is members-only; a guest gets the apply gate.
        $this->assertSame(0, substr_count($html, 'data-close-dialog'));
        $this->assertSame(0, substr_count($html, 'id="apply-form"'));
        $this->assertSame(3, substr_count($html, 'data-tab="'));

        foreach (config('jobs_demo') as $job) {
            $response
                ->assertSee($job['title'])
                ->assertSee(route('jobs.show', $job['id']), false);
        }
    }

    public function test_every_configured_job_has_a_public_detail_page(): void
    {
        foreach (config('jobs_demo') as $job) {
            $this->get(route('jobs.show', $job['id']))
                ->assertOk()
                ->assertSee($job['title'])
                ->assertSee($job['company'])
                ->assertSee('Back to all jobs')
                ->assertSee('js/job-show.js', false);
        }
    }

    public function test_jobs_index_uses_directory_search_without_changing_home_search(): void
    {
        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('Job Directory')
            ->assertSee('Search jobs by role or company')
            ->assertSee('id="job-mode-select"', false)
            ->assertDontSee('Popular:');

        $this->get('/')
            ->assertOk()
            ->assertSee('Search opportunities')
            ->assertSee('Popular:')
            ->assertDontSee('id="job-mode-select"', false)
            ->assertDontSee('Job Directory');
    }

    public function test_home_page_links_to_every_full_job_page(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (config('jobs_demo') as $job) {
            $response->assertSee(route('jobs.show', $job['id']), false);
        }
    }

    public function test_frontend_job_navigation_uses_the_dedicated_jobs_index(): void
    {
        $jobsIndexUrl = route('jobs.index');

        foreach (['/', $jobsIndexUrl, route('jobs.show', 'software-engineer')] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertGreaterThanOrEqual(
                2,
                substr_count($html, 'href="' . $jobsIndexUrl . '"'),
                "Expected the header and footer on {$url} to link to the jobs index."
            );
        }

        $this->get($jobsIndexUrl)
            ->assertSee('class="is-active" href="' . $jobsIndexUrl . '"', false);
    }

    public function test_job_pages_load_only_their_page_specific_assets(): void
    {
        $listingPages = ['/', route('jobs.index')];

        foreach ($listingPages as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, 'src="' . asset('js/jobs.js') . '"'));
            $this->assertSame(0, substr_count($html, 'src="' . asset('js/job-show.js') . '"'));
            $this->assertSame(1, substr_count($html, 'href="' . asset('css/jobs.css') . '"'));
            $this->assertSame(0, substr_count($html, 'href="' . asset('css/job-show.css') . '"'));
        }

        $jobsIndexHtml = $this->get(route('jobs.index'))->assertOk()->getContent();
        $this->assertSame(0, substr_count($jobsIndexHtml, 'href="' . asset('css/job-search.css') . '"'));
        $this->assertSame(1, substr_count($jobsIndexHtml, 'href="' . asset('css/job-directory.css') . '"'));

        $homeHtml = $this->get('/')->assertOk()->getContent();
        $this->assertSame(0, substr_count($homeHtml, 'href="' . asset('css/job-directory.css') . '"'));

        $jobDetailHtml = $this->get(route('jobs.show', 'software-engineer'))->assertOk()->getContent();
        $this->assertSame(0, substr_count($jobDetailHtml, 'src="' . asset('js/jobs.js') . '"'));
        $this->assertSame(1, substr_count($jobDetailHtml, 'src="' . asset('js/job-show.js') . '"'));
        $this->assertSame(1, substr_count($jobDetailHtml, 'href="' . asset('css/jobs.css') . '"'));
        $this->assertSame(0, substr_count($jobDetailHtml, 'href="' . asset('css/job-search.css') . '"'));
        $this->assertSame(0, substr_count($jobDetailHtml, 'href="' . asset('css/job-directory.css') . '"'));
        $this->assertSame(1, substr_count($jobDetailHtml, 'href="' . asset('css/job-show.css') . '"'));
    }

    public function test_frontend_pages_include_scroll_navigation_controls(): void
    {
        foreach (['/', route('jobs.index'), route('jobs.show', 'software-engineer')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-scroll-controls', false)
                ->assertSee('data-scroll-to="top"', false)
                ->assertSee('data-scroll-to="bottom"', false);
        }
    }

    public function test_unknown_or_invalid_job_slug_returns_not_found(): void
    {
        $this->get('/jobs/not-a-real-job')->assertNotFound();
        $this->get('/jobs/INVALID_SLUG')->assertNotFound();
    }

    public function test_related_jobs_do_not_link_back_to_the_selected_job(): void
    {
        $job = config('jobs_demo.1');

        $this->get(route('jobs.show', $job['id']))
            ->assertOk()
            ->assertDontSee('href="' . route('jobs.show', $job['id']) . '"', false);
    }

    public function test_job_catalog_has_unique_url_safe_ids_and_required_content(): void
    {
        $jobs = config('jobs_demo');
        $requiredKeys = [
            'id', 'title', 'company', 'location', 'salary', 'short_salary', 'summary',
            'type', 'mode', 'experience', 'department', 'deadline', 'posted',
            'posted_days', 'applicants', 'featured', 'highlighted', 'logo', 'badges',
            'tabs', 'detail_items', 'quick_apply',
        ];

        $this->assertNotEmpty($jobs);
        $this->assertSameSize($jobs, array_unique(array_column($jobs, 'id')));

        foreach ($jobs as $job) {
            $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $job['id']);

            foreach ($requiredKeys as $key) {
                $this->assertArrayHasKey($key, $job);
            }

            // The third panel was `company`; the Company tab now renders the
            // employer's own profile, so that panel became extra job copy.
            foreach (['description', 'requirements', 'job_description'] as $tab) {
                $this->assertArrayHasKey($tab, $job['tabs']);
                $this->assertArrayHasKey('title', $job['tabs'][$tab]);
                $this->assertArrayHasKey('body', $job['tabs'][$tab]);
                $this->assertArrayHasKey('list_title', $job['tabs'][$tab]);
                $this->assertArrayHasKey('list', $job['tabs'][$tab]);
            }
        }
    }
}
