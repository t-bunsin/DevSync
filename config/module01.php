<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Replace the legacy users table
    |--------------------------------------------------------------------------
    |
    | Module 01 changes users.id from BIGINT to UUID, so its table cannot
    | coexist with the legacy one — adopting it drops whatever is there. The
    | migration refuses to do that unless this is explicitly true.
    |
    | It is read through config rather than env() directly because env() returns
    | null once `php artisan config:cache` has run, which silently turned the
    | opt-in off and made the migration refuse even with the value set in .env.
    |
    */

    'replace_users' => env('MODULE01_REPLACE_USERS', false),

];
