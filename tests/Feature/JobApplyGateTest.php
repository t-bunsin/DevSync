<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Browsing the board stays open to everyone. Applying is the one candidate
 * action behind an account, so these cover the gate in both directions.
 */
class JobApplyGateTest extends TestCase
{
    private function job(): array
    {
        return config('jobs_demo.0');
    }

    /**
     * Unsaved on purpose: the gate only reads the session guard, so these
     * stay runnable without a migrated database.
     */
    private function candidate(): User
    {
        $user = new User();
        $user->id = 1;
        $user->display_name = 'Dara Candidate';
        $user->email = 'dara@example.com';

        return $user;
    }

    public function test_guest_is_sent_to_registration_and_kept_on_the_job(): void
    {
        $job = $this->job();

        $this->get(route('jobs.apply', $job['id']))
            ->assertRedirect(route('register'))
            ->assertSessionHas('url.intended', route('jobs.show', $job['id']) . '?apply=1')
            ->assertSessionHas('status');
    }

    public function test_member_reaches_the_job_with_the_application_form_open(): void
    {
        $job = $this->job();

        $this->actingAs($this->candidate())
            ->get(route('jobs.apply', $job['id']))
            ->assertRedirect(route('jobs.show', $job['id']) . '?apply=1');
    }

    public function test_apply_gate_rejects_an_unknown_job(): void
    {
        $this->get('/jobs/not-a-real-job/apply')->assertNotFound();
    }

    public function test_guest_job_page_offers_the_gate_instead_of_the_form(): void
    {
        $job = $this->job();

        $this->get(route('jobs.show', $job['id']))
            ->assertOk()
            ->assertSee('Register to apply')
            ->assertSee('href="' . route('jobs.apply', $job['id']) . '"', false)
            ->assertDontSee('id="job-page-apply-form"', false)
            ->assertDontSee('id="job-page-apply-button"', false);
    }

    public function test_member_job_page_keeps_the_application_form(): void
    {
        $job = $this->job();

        $this->actingAs($this->candidate())
            ->get(route('jobs.show', $job['id']))
            ->assertOk()
            ->assertSee('Apply for this role')
            ->assertSee('id="job-page-apply-form"', false)
            ->assertDontSee('Register to apply');
    }

    public function test_returning_from_registration_opens_the_form_automatically(): void
    {
        $job = $this->job();

        $this->actingAs($this->candidate())
            ->get(route('jobs.show', $job['id']) . '?apply=1')
            ->assertOk()
            ->assertSee('data-job-page-auto-open', false);
    }

    public function test_board_detail_apply_control_follows_the_same_rule(): void
    {
        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('Register to apply')
            ->assertDontSee('id="apply-form"', false);

        $this->actingAs($this->candidate())
            ->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('id="apply-form"', false)
            ->assertDontSee('Register to apply');
    }
}
