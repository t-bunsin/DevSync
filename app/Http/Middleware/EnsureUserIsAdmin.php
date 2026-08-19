<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the back-office screens that manage accounts. Pair it with `auth`:
 * this one answers "which signed-in users may be here", not "is anyone signed
 * in", so it 403s rather than redirecting to the login form.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'This area is for administrators.');

        return $next($request);
    }
}
