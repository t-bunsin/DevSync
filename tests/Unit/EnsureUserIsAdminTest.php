<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * The gate on the account-management screens. Database-free: the middleware
 * only ever asks the request who is signed in.
 */
class EnsureUserIsAdminTest extends TestCase
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

    private function pass(?User $user): Response
    {
        $request = Request::create('/users');
        $request->setUserResolver(fn () => $user);

        return (new EnsureUserIsAdmin())->handle($request, fn () => new Response('reached'));
    }

    /** HttpException carries the status on getStatusCode(), not getCode(). */
    private function assertRefused(?User $user): void
    {
        try {
            $this->pass($user);
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());

            return;
        }

        $this->fail('Expected the request to be refused.');
    }

    public function test_an_admin_is_let_through(): void
    {
        $this->assertSame('reached', $this->pass($this->viewer(Role::ADMIN))->getContent());
    }

    public function test_a_job_seeker_is_refused(): void
    {
        $this->assertRefused($this->viewer(Role::EMPLOYEE));
    }

    public function test_an_employer_is_refused(): void
    {
        $this->assertRefused($this->viewer(Role::EMPLOYER));
    }

    public function test_a_user_with_no_roles_is_refused(): void
    {
        $this->assertRefused($this->viewer());
    }

    public function test_a_guest_is_refused(): void
    {
        $this->assertRefused(null);
    }
}
