<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Resume;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The resume.* switches on the User Role & Permission page have to bite for a
 * job seeker too. Ownership decides *which* resume they may touch; the grant
 * decides whether they may touch one at all — before this, ownership stood in
 * for the grant and the switches did nothing on that row.
 */
class ResumePermissionTest extends TestCase
{
    use RefreshDatabase;

    private function candidate(): User
    {
        $user = User::factory()->create();
        $user->syncRoles([Role::EMPLOYEE], Role::EMPLOYEE);

        return $user->fresh()->load('roles.permissions');
    }

    private function resumeFor(User $user): Resume
    {
        return Resume::create([
            'full_name' => 'Dara Candidate',
            'created_by' => $user->id,
        ]);
    }

    private function revoke(string $code): void
    {
        $role = Role::where('code', Role::EMPLOYEE)->firstOrFail();

        $role->permissions()->detach(Permission::where('code', $code)->value('id'));
    }

    public function test_job_seeker_reads_their_own_resume_while_the_grant_stands(): void
    {
        $user = $this->candidate();
        $resume = $this->resumeFor($user);

        $this->actingAs($user)->get(route('resumes.index'))->assertOk();
        $this->actingAs($user)->get(route('resumes.show', $resume))->assertOk();
    }

    public function test_switching_resume_view_off_closes_the_register_and_their_own_resume(): void
    {
        $user = $this->candidate();
        $resume = $this->resumeFor($user);

        $this->revoke(Permission::RESUME_VIEW);
        $user = $user->fresh()->load('roles.permissions');

        $this->actingAs($user)->get(route('resumes.index'))->assertForbidden();
        $this->actingAs($user)->get(route('resumes.show', $resume))->assertForbidden();
    }

    public function test_ownership_still_narrows_a_job_seeker_who_holds_the_grant(): void
    {
        $user = $this->candidate();
        $stranger = $this->candidate();

        $this->actingAs($user)
            ->get(route('resumes.show', $this->resumeFor($stranger)))
            ->assertForbidden();
    }

    public function test_switching_resume_edit_off_stops_a_job_seeker_editing_their_own(): void
    {
        $user = $this->candidate();
        $resume = $this->resumeFor($user);

        $this->actingAs($user)->get(route('resumes.edit', $resume))->assertOk();

        $this->revoke(Permission::RESUME_EDIT);

        $this->actingAs($user->fresh()->load('roles.permissions'))
            ->get(route('resumes.edit', $resume))
            ->assertForbidden();
    }

    /** Deleting is refused for a candidate whatever the switch says. */
    public function test_job_seeker_cannot_delete_even_with_the_grant(): void
    {
        $user = $this->candidate();
        $resume = $this->resumeFor($user);

        Role::where('code', Role::EMPLOYEE)->firstOrFail()
            ->permissions()->syncWithoutDetaching([Permission::where('code', Permission::RESUME_DELETE)->value('id')]);

        $this->actingAs($user->fresh()->load('roles.permissions'))
            ->delete(route('resumes.destroy', $resume))
            ->assertForbidden();

        $this->assertDatabaseHas('resumes', ['id' => $resume->id]);
    }
}
