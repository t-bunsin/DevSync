<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for back-office screens shared by more than one non-admin role.
 * Admins always pass, regardless of which roles are listed, so this only
 * needs the roles that are actually allowed alongside them — e.g.
 * `role:employer`. Pair with `auth`, same as EnsureUserIsAdmin.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->isAdmin() || collect($roles)->contains(fn (string $role) => $user->hasRole($role))),
            403,
            'You do not have access to this area.'
        );

        return $next($request);
    }
}
