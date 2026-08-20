<?php

/*
| KH-WORKS | Pricing tiers
|
| Single source of truth for the plan grid shown on the public pricing
| section (resources/views/main.blade.php) and the "choose a package" step
| of account billing (App\Http\Controllers\BillingController). Keeping the
| tier facts here means both places price the same plan identically instead
| of two hand-typed arrays drifting apart.
|
| Page-specific bits (marketing CTA/href, billing "select" button) are added
| by whichever view renders these, not stored here.
*/
return [
    // Applied to the monthly price to get the annual (per-month) price.
    'annual_discount' => 0.20,

    // Listed cheapest first, left to right.
    'tiers' => [
        [
            'id' => 'revenue-share',
            'name' => 'Revenue Share',
            'monthly' => 0,
            'blurb' => 'For a new board finding its first employers.',
            'featured' => false,
            'features' => [
                ['label' => '25% Revenue Share', 'highlight' => true],
                ['label' => '1 Job Board', 'highlight' => false],
                ['label' => 'Unlimited Pageviews', 'highlight' => false],
                ['label' => 'Email Support', 'highlight' => false],
                ['label' => 'Custom Domains', 'highlight' => false],
                ['label' => '2 Custom Pages', 'highlight' => false],
                ['label' => 'Employer Profiles & Directory', 'highlight' => false],
            ],
        ],
        [
            'id' => 'single-site',
            'name' => 'Single Site',
            'monthly' => 1,
            'blurb' => 'For one board with steady traffic and hiring.',
            'featured' => true,
            'features' => [
                ['label' => 'No Revenue Share', 'highlight' => true],
                ['label' => '1 Job Board', 'highlight' => false],
                ['label' => '40,000 Pageviews / Month', 'highlight' => false],
                ['label' => 'Email Support', 'highlight' => false],
                ['label' => 'Custom Domains', 'highlight' => false],
                ['label' => 'Unlimited Custom Pages and Blogs', 'highlight' => false],
                ['label' => 'Employer Profiles & Directory', 'highlight' => false],
            ],
        ],
        [
            'id' => 'multi-site',
            'name' => 'Multi Site',
            'monthly' => 1,
            'blurb' => 'For agencies running several boards at once.',
            'featured' => false,
            'features' => [
                ['label' => 'No Revenue Share', 'highlight' => true],
                ['label' => '3 Job Boards', 'highlight' => false],
                ['label' => '150,000 Pageviews / Month', 'highlight' => false],
                ['label' => 'Email & Phone Support', 'highlight' => false],
                ['label' => 'Custom Domains', 'highlight' => false],
                ['label' => 'Unlimited Custom Pages and Blogs', 'highlight' => false],
                ['label' => 'Employer Profiles & Directory', 'highlight' => false],
            ],
        ],
    ],
];
