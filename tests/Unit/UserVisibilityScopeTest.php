<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

/**
 * Admin accounts must be invisible to everyone else. The rule lives in a query
 * scope rather than the view, so these assert the SQL it produces — no rows
 * required, which keeps the guarantee covered even though this project's
 * migrations cannot currently run on the sqlite test connection.
 */
class UserVisibilityScopeTest extends TestCase
{
    private function viewer(string ...$roleCodes): User
    {
        $user = new User();

        // Set rather than loaded, so isAdmin() never reaches for a database.
        $user->setRelation('roles', collect(array_map(
            fn (string $code) => new Role(['code' => $code]),
            $roleCodes
        )));

        return $user;
    }

    public function test_admin_sees_the_whole_directory(): void
    {
        $query = User::query()->visibleTo($this->viewer(Role::ADMIN));

        $this->assertStringNotContainsString('not exists', $query->toSql());
        $this->assertNotContains(Role::ADMIN, $query->getBindings());
    }

    public function test_job_seeker_never_sees_an_admin_account(): void
    {
        $query = User::query()->visibleTo($this->viewer(Role::EMPLOYEE));

        $this->assertStringContainsString('not exists', $query->toSql());
        $this->assertContains(Role::ADMIN, $query->getBindings());
    }

    public function test_employer_never_sees_an_admin_account(): void
    {
        $query = User::query()->visibleTo($this->viewer(Role::EMPLOYER));

        $this->assertStringContainsString('not exists', $query->toSql());
        $this->assertContains(Role::ADMIN, $query->getBindings());
    }

    public function test_a_guest_viewer_is_treated_as_the_least_privileged(): void
    {
        $query = User::query()->visibleTo(null);

        $this->assertStringContainsString('not exists', $query->toSql());
    }

    public function test_role_filter_only_constrains_when_a_code_is_given(): void
    {
        $this->assertStringNotContainsString('exists', User::query()->withRole(null)->toSql());
        $this->assertStringContainsString('exists', User::query()->withRole(Role::EMPLOYER)->toSql());
    }

    public function test_search_matches_the_columns_the_directory_shows(): void
    {
        $sql = User::query()->search('dara')->toSql();

        foreach (['display_name', 'first_name', 'last_name', 'email', 'phone'] as $column) {
            $this->assertStringContainsString($column, $sql);
        }

        $this->assertStringNotContainsString('like', User::query()->search('   ')->toSql());
    }
}
