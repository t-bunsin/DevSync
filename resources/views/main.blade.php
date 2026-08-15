@extends('layouts.master')

@section('title', 'KH-WORKS | Find Your Next Opportunity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}?v={{ filemtime(public_path('css/pricing.css')) }}">
@endpush

@section('content')
    <section class="jf-hero" aria-labelledby="hero-title">
        <div class="jf-hero__glow jf-hero__glow--one" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--two" aria-hidden="true"></div>

        <div class="jf-shell jf-hero__inner">
            <div class="jf-hero__copy">
                <span class="jf-hero__eyebrow">
                    <i class="fas fa-sparkles" aria-hidden="true"></i>
                    Cambodia’s career marketplace
                </span>
                <h1 id="hero-title">Find work that moves <span>you forward.</span></h1>
                <p>Explore verified opportunities from trusted teams in Cambodia and remote-first companies across Southeast Asia.</p>

                <div class="jf-hero__actions">
                    <a class="jf-btn jf-btn--accent" href="#jobs">
                        Explore jobs
                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                    </a>
                    <a class="jf-btn jf-btn--hero-ghost" href="{{ route('register') }}">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Post a job
                    </a>
                </div>

                <dl class="jf-hero__metrics" aria-label="Platform statistics">
                    <div><dt>1,200+</dt><dd>Active roles</dd></div>
                    <div><dt>320+</dt><dd>Hiring teams</dd></div>
                    <div><dt>3 days</dt><dd>Avg. response</dd></div>
                </dl>
            </div>

            <aside class="jf-spotlight" aria-label="Featured opportunities" aria-roledescription="carousel" data-spotlight>
                <div class="jf-spotlight__topline">
                    <span><i class="fas fa-bolt" aria-hidden="true"></i> Featured this week</span>
                    <span class="jf-spotlight__status">Actively hiring</span>
                </div>

                <div class="jf-spotlight__slides">
                    @foreach ($spotlightJobs as $index => $spotlightJob)
                        <article
                            class="jf-spotlight__slide{{ $index === 0 ? ' is-active' : '' }}"
                            data-spotlight-slide
                            role="group"
                            aria-roledescription="slide"
                            aria-label="{{ $index + 1 }} of {{ count($spotlightJobs) }}"
                            @unless ($index === 0) aria-hidden="true" @endunless
                        >
                            <div class="jf-spotlight__company">
                                <div class="jf-logo jf-logo--{{ $spotlightJob['logo'] }}">
                                    @if ($spotlightJob['logo'] === 'aba')
                                        <span>ABA</span><small>BANK</small>
                                    @elseif ($spotlightJob['logo'] === 'tech')
                                        <i class="fas fa-wifi" aria-hidden="true"></i>
                                    @else
                                        <span>D</span>
                                    @endif
                                </div>
                                <div>
                                    <span>{{ $spotlightJob['company'] }}</span>
                                    <strong>{{ $spotlightJob['title'] }}</strong>
                                </div>
                            </div>

                            <div class="jf-spotlight__meta">
                                <span><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $spotlightJob['mode'] }}</span>
                                <span><i class="fas fa-clock" aria-hidden="true"></i> {{ $spotlightJob['type'] }}</span>
                                <span><i class="fas fa-code" aria-hidden="true"></i> {{ $spotlightJob['department'] }}</span>
                            </div>

                            <div class="jf-spotlight__footer">
                                <div><small>Salary range</small><strong>{{ $spotlightJob['short_salary'] }}</strong></div>
                                <a href="#jobs" data-view-job="{{ $spotlightJob['id'] }}">View role <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="jf-spotlight__controls">
                    <button class="jf-spotlight__arrow" type="button" data-spotlight-prev aria-label="Previous featured role">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    </button>

                    <div class="jf-spotlight__dots" role="tablist" aria-label="Choose a featured role">
                        @foreach ($spotlightJobs as $index => $spotlightJob)
                            <button
                                class="jf-spotlight__dot{{ $index === 0 ? ' is-active' : '' }}"
                                type="button"
                                role="tab"
                                data-spotlight-dot="{{ $index }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="{{ $spotlightJob['title'] }} at {{ $spotlightJob['company'] }}"
                            ></button>
                        @endforeach
                    </div>

                    <button class="jf-spotlight__arrow" type="button" data-spotlight-next aria-label="Next featured role">
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </div>
            </aside>
        </div>
    </section>

    <section class="jf-job-ticker" aria-label="Popular job categories">
        <p class="visually-hidden">Popular job categories include software engineering, product design, retail banking, data and analytics, customer support, marketing, finance, and remote work.</p>
        <div class="jf-job-ticker__viewport" aria-hidden="true">
            <div class="jf-job-ticker__track">
                @for ($tickerCopy = 0; $tickerCopy < 2; $tickerCopy++)
                    <div class="jf-job-ticker__group">
                        <span>Software Engineering</span>
                        <span>Product Design</span>
                        <span>Retail Banking</span>
                        <span>Data &amp; Analytics</span>
                        <span>Customer Support</span>
                        <span>Digital Marketing</span>
                        <span>Finance</span>
                        <span>Remote Roles</span>
                    </div>
                @endfor
            </div>
        </div>
    </section>

    @php
        $howItWorks = [
            [
                'title' => 'Quick profile',
                'body' => 'Fill six fields about your background — it takes under two minutes.',
                'icon' => 'fa-user-pen',
                'note' => '2 min',
            ],
            [
                'title' => 'See matched jobs',
                'body' => 'We surface the roles that fit your experience, skills, and location.',
                'icon' => 'fa-wand-magic-sparkles',
                'note' => 'Instant',
            ],
            [
                'title' => 'Create account',
                'body' => 'Only needed once you are ready to apply — browse the board freely first.',
                'icon' => 'fa-user-plus',
                'note' => 'Optional',
            ],
            [
                'title' => 'Apply & get hired',
                'body' => 'Complete your profile once, then apply to any role in seconds.',
                'icon' => 'fa-paper-plane',
                'note' => 'One click',
            ],
        ];
    @endphp

    <section class="jf-steps" id="how-it-works" aria-labelledby="steps-title">
        <div class="jf-shell">
            <div class="jf-steps__heading">
                <div>
                    <span class="jf-kicker">How it works</span>
                    <h2 id="steps-title">Find your dream job in {{ count($howItWorks) }} steps</h2>
                </div>
                <p>From a two-minute profile to a sent application — no account required until you apply.</p>
            </div>

            <ol class="jf-steps__list">
                @foreach ($howItWorks as $index => $step)
                    <li class="jf-step">
                        <div class="jf-step__top">
                            <span class="jf-step__number" aria-hidden="true">{{ $index + 1 }}</span>
                            <span class="jf-step__rail" aria-hidden="true"></span>
                            <span class="jf-step__icon" aria-hidden="true"><i class="fas {{ $step['icon'] }}"></i></span>
                        </div>

                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['body'] }}</p>
                        <span class="jf-step__note">{{ $step['note'] }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <div data-jobs-explorer>
        <section class="jf-trusted" id="companies" aria-labelledby="companies-title">
            <div class="jf-shell jf-trusted__inner">
                <p id="companies-title">Opportunities from teams shaping Cambodia</p>
                <div class="jf-trusted__logos" aria-label="Featured employers">
                    <span><b>ABA</b> Bank</span>
                    <span><i class="fas fa-signal" aria-hidden="true"></i> Smart</span>
                    <span><i class="fas fa-circle-nodes" aria-hidden="true"></i> Wing</span>
                    <span><i class="fas fa-building" aria-hidden="true"></i> Chip Mong</span>
                    <span><i class="fas fa-shield-heart" aria-hidden="true"></i> AIA</span>
                </div>
            </div>
        </section>

        @include('jobs.partials.catalog')
    </div>

    @php
        // Annual billing is the monthly rate less this much, so a price change
        // only ever has to be made in one place: the 'monthly' key below.
        $annualDiscount = 0.20;

        // Listed cheapest first, left to right.
        $plans = [
            [
                'id' => 'revenue-share',
                'name' => 'Revenue Share',
                'monthly' => 0,
                'blurb' => 'For a new board finding its first employers.',
                'cta' => 'Start free',
                'href' => route('register'),
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
                'monthly' => 99,
                'blurb' => 'For one board with steady traffic and hiring.',
                'cta' => 'Choose Single Site',
                'href' => route('register'),
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
                'monthly' => 249,
                'blurb' => 'For agencies running several boards at once.',
                'cta' => 'Talk to sales',
                'href' => route('contact'),
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
        ];

        // Both billing periods are derived here rather than typed twice, so the
        // annual column can never drift out of step with the monthly one.
        $plans = array_map(function (array $plan) use ($annualDiscount) {
            $monthly = $plan['monthly'];
            $annual = (int) round($monthly * (1 - $annualDiscount));

            $plan['amounts'] = [
                'monthly' => [
                    'price' => '$' . number_format($monthly),
                    'note' => $monthly === 0 ? 'Free forever' : 'Billed monthly',
                ],
                'annual' => [
                    'price' => '$' . number_format($annual),
                    'note' => $monthly === 0
                        ? 'Free forever'
                        : 'Billed $' . number_format($annual * 12) . ' per year',
                ],
            ];

            return $plan;
        }, $plans);

        $savingLabel = 'Save ' . round($annualDiscount * 100) . '%';
    @endphp

    <section class="jf-pricing" id="pricing" aria-labelledby="pricing-title">
        <div class="jf-shell">
            <div class="jf-pricing__heading">
                <span class="jf-kicker">Pricing</span>
                <h2 id="pricing-title">Plans that grow with your job board</h2>
                <p>Start free and share revenue, or switch to a flat monthly fee once your traffic makes that the cheaper deal.</p>
            </div>

            {{-- Hidden until pricing.js takes over, so a control that cannot
                 work without scripting is never shown. Monthly is the markup
                 default, which is what a no-script visitor keeps. --}}
            <div class="jf-pricing__billing" data-billing-toggle hidden>
                <div class="jf-pricing__switch" role="group" aria-label="Billing period">
                    <button type="button" data-period="monthly" aria-pressed="true">Monthly</button>
                    <button type="button" data-period="annual" aria-pressed="false">
                        Annual<span class="jf-pricing__save">{{ $savingLabel }}</span>
                    </button>
                </div>
            </div>

            <div class="jf-pricing__grid">
                @foreach ($plans as $plan)
                    <article @class(['jf-plan', 'jf-plan--featured' => $plan['featured']])
                        aria-labelledby="plan-{{ $plan['id'] }}">
                        @if ($plan['featured'])
                            <span class="jf-plan__badge">Most popular</span>
                        @endif

                        <h3 class="jf-plan__name" id="plan-{{ $plan['id'] }}">{{ $plan['name'] }}</h3>

                        <p class="jf-plan__price">
                            <strong data-amount
                                data-monthly="{{ $plan['amounts']['monthly']['price'] }}"
                                data-annual="{{ $plan['amounts']['annual']['price'] }}">{{ $plan['amounts']['monthly']['price'] }}</strong><span>/month</span>
                        </p>

                        <p class="jf-plan__billing-note" data-amount
                            data-monthly="{{ $plan['amounts']['monthly']['note'] }}"
                            data-annual="{{ $plan['amounts']['annual']['note'] }}">{{ $plan['amounts']['monthly']['note'] }}</p>

                        <p class="jf-plan__blurb">{{ $plan['blurb'] }}</p>

                        <ul class="jf-plan__features">
                            @foreach ($plan['features'] as $feature)
                                <li @class(['is-highlight' => $feature['highlight']])>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>{{ $feature['label'] }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a @class(['jf-btn', 'jf-plan__cta', 'jf-plan__cta--solid' => $plan['featured']])
                            href="{{ $plan['href'] }}">
                            {{ $plan['cta'] }}<span class="visually-hidden"> — {{ $plan['name'] }}, <span data-amount
                                    data-monthly="{{ $plan['amounts']['monthly']['price'] }}"
                                    data-annual="{{ $plan['amounts']['annual']['price'] }}">{{ $plan['amounts']['monthly']['price'] }}</span> per month</span>
                        </a>
                    </article>
                @endforeach
            </div>

            <p class="jf-pricing__note">All plans include employer profiles and the public directory. Cancel or change plan at any time.</p>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/jobs.js') }}?v={{ filemtime(public_path('js/jobs.js')) }}"></script>
    <script src="{{ asset('js/pricing.js') }}?v={{ filemtime(public_path('js/pricing.js')) }}"></script>
@endpush
