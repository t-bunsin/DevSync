<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // PayWay posts here server-to-server, with no Laravel session/CSRF
        // token to present. Authenticity instead relies on recomputing the
        // hash — see BillingController::paywayCallback().
        'admin/account-billing/payway/callback',
    ];
}
