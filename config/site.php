<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chat Widget
    |--------------------------------------------------------------------------
    |
    | Destination for the floating "Chat with us" button rendered on every
    | frontend page. Set CHAT_URL in your .env to point at your own account.
    | Leave it empty to hide the button entirely.
    |
    */

    'chat_url' => env('CHAT_URL', 'https://t.me/khworks'),

    /*
    |--------------------------------------------------------------------------
    | Social Profiles
    |--------------------------------------------------------------------------
    |
    | Shown as icons in the footer. Each one is rendered only when it holds a
    | URL, so an unset network is left out rather than linking nowhere. The
    | Telegram icon reuses chat_url above.
    |
    */

    'social' => [
        'facebook' => env('SOCIAL_FACEBOOK'),
        'linkedin' => env('SOCIAL_LINKEDIN'),
    ],

];
