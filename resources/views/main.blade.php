@extends('layouts.master')

@section('title', 'ZIN-WORKS | Find Your Next Opportunity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}?v={{ filemtime(public_path('css/pricing.css')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="{{ asset('css/role-rack.css') }}?v={{ filemtime(public_path('css/role-rack.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/hero-paths.css') }}?v={{ filemtime(public_path('css/hero-paths.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/hero-seeker.css') }}?v={{ filemtime(public_path('css/hero-seeker.css')) }}">
    <link rel="preload" as="image" href="{{ asset('img/hero-job-seeker.png') }}">
@endpush

@section('content')
    <section class="jf-hero jf-hero--rack" aria-labelledby="hero-title">
        <div class="jf-hero__glow jf-hero__glow--one" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--two" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--three" aria-hidden="true"></div>
        <div class="jf-hero__beam" aria-hidden="true"></div>

        <div class="jf-shell jf-hero__inner">
            <div class="jf-hero__copy">
                <span class="jf-hero__eyebrow">
                    {{-- A live pulse rather than an icon: the board is always moving. --}}
                    <span class="jf-hero__eyebrow-dot" aria-hidden="true"></span>
                    Cambodia’s career marketplace
                </span>
                <h1 id="hero-title">
                    <span class="jf-hero__word">Find</span>
                    <span class="jf-hero__word">work</span>
                    <span class="jf-hero__word">that</span>
                    <span class="jf-hero__word">moves</span>
                    <span class="jf-hero__accent">you forward.</span>
                </h1>

                {{-- Two paths, one per audience. Each tile loops a small scene of
                     what happens next: a search landing on a match, or a post
                     being written, stamped and sent. --}}
                <div class="jf-hero__actions jf-paths">
                    <a class="jf-path jf-path--seek" href="#jobs">
                        <span class="jf-path__scene" aria-hidden="true">
                            <span class="jf-seek__row jf-seek__row--1"><i></i><b></b></span>
                            <span class="jf-seek__row jf-seek__row--2"><i></i><b></b></span>
                            <span class="jf-seek__row jf-seek__row--3"><i></i><b></b></span>
                            <span class="jf-seek__lens"></span>
                            <span class="jf-seek__match"><i class="fas fa-check"></i></span>
                        </span>
                        <span class="jf-path__text">
                            <small>I’m looking for a job</small>
                            <strong>Explore jobs</strong>
                        </span>
                        <span class="jf-path__go" aria-hidden="true"><i class="fas fa-arrow-down"></i></span>
                    </a>

                    <a class="jf-path jf-path--post" href="{{ route('register') }}">
                        <span class="jf-path__scene" aria-hidden="true">
                            <span class="jf-post__doc">
                                <span class="jf-post__line jf-post__line--1"></span>
                                <span class="jf-post__line jf-post__line--2"></span>
                                <span class="jf-post__line jf-post__line--3"></span>
                                <span class="jf-post__stamp"><i class="fas fa-check"></i></span>
                            </span>
                            <span class="jf-post__plane"><i class="fas fa-paper-plane"></i></span>
                        </span>
                        <span class="jf-path__text">
                            <small>I’m hiring</small>
                            <strong>Post a job</strong>
                        </span>
                        <span class="jf-path__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                    </a>
                </div>

                <dl class="jf-hero__metrics" aria-label="Platform statistics">
                    <div><dt data-count-up>1,200+</dt><dd>Active roles</dd></div>
                    <div><dt data-count-up>320+</dt><dd>Hiring teams</dd></div>
                    <div><dt data-count-up>3 days</dt><dd>Avg. response</dd></div>
                </dl>
            </div>

            <div class="jf-hero__stage">
                {{-- The roles hang off a rail here; role-rack.js swings them. --}}
                @include('partials.role-rack')

                {{-- A candidate who made it, sitting under the rack, her JOB sign
                     pointing at the intro line beside her. The ring and sparkles
                     sit over the PASS stamp in the artwork, so their positions
                     are percentages of the image. --}}
                <div class="jf-hero__stage-foot">
                    <figure class="jf-seeker" aria-hidden="true">
                        <img src="{{ asset('img/hero-job-seeker.png') }}" alt="" width="504" height="507" loading="eager" decoding="async">
                        <span class="jf-seeker__ping"></span>
                        <span class="jf-seeker__spark jf-seeker__spark--1">✦</span>
                        <span class="jf-seeker__spark jf-seeker__spark--2">✦</span>
                        <span class="jf-seeker__spark jf-seeker__spark--3">✦</span>
                        <span class="jf-seeker__bubble">
                            <span class="jf-seeker__say jf-seeker__say--1"><i class="fas fa-trophy"></i> Got the job!</span>
                            <span class="jf-seeker__say jf-seeker__say--2"><i class="fas fa-circle-check"></i> Test passed</span>
                            <span class="jf-seeker__say jf-seeker__say--3"><i class="fas fa-arrow-left"></i> Your turn</span>
                        </span>
                    </figure>

                    <p class="jf-hero__lede">Explore verified opportunities from trusted teams in Cambodia and remote-first companies across Southeast Asia.</p>
                </div>
            </div>
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

   {{-- A plain grid of cards here: the landing page has no detail panel, so
        every card links straight through to its job page. --}}
   @include('jobs.partials.catalog', ['interactive' => false])

    @php
        // Annual billing is the monthly rate less this much, so a price change
        // only ever has to be made in one place: config/plans.php.
        $annualDiscount = config('plans.annual_discount');

        // Tier facts (name, price, features) live in config/plans.php, shared
        // with the account billing "choose a package" step. The marketing CTA
        // and href are specific to this page, so they're attached here.
        $ctas = [
            'revenue-share' => ['cta' => 'Start free', 'href' => route('register')],
            'single-site' => ['cta' => 'Choose Single Site', 'href' => route('register')],
            'multi-site' => ['cta' => 'Talk to sales', 'href' => route('contact')],
        ];

        $plans = array_map(
            fn (array $plan) => [...$plan, ...$ctas[$plan['id']]],
            config('plans.tiers')
        );

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
     <section class="jf-steps" id="how-it-works" aria-labelledby="steps-title">
        <div class="jf-shell">
            <div class="jf-steps__panel">
                <div class="jf-steps__heading">
                    <div>
                        <span class="jf-kicker">How it works</span>
                        <h2 id="steps-title">Find your dream job in <em>{{ count($howItWorks) }} steps</em></h2>
                    </div>
                    <p>From a two-minute profile to a sent application — no account required until you apply.</p>
                </div>

                <ol class="jf-steps__list">
                    @foreach ($howItWorks as $index => $step)
                        <li class="jf-step" style="--jf-step-index: {{ $index }}">
                            <span class="jf-step__connector" aria-hidden="true"></span>

                            <div class="jf-step__top">
                                <span class="jf-step__number" aria-hidden="true">{{ $index + 1 }}</span>
                                <span class="jf-step__icon" aria-hidden="true"><i class="fas {{ $step['icon'] }}"></i></span>
                            </div>

                            <h3>{{ $step['title'] }}</h3>
                            <p>{{ $step['body'] }}</p>
                            <span class="jf-step__note">{{ $step['note'] }}</span>
                            <span class="jf-step__ghost" aria-hidden="true">{{ $index + 1 }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/jobs.js') }}?v={{ filemtime(public_path('js/jobs.js')) }}"></script>
    <script src="{{ asset('js/pricing.js') }}?v={{ filemtime(public_path('js/pricing.js')) }}"></script>
    <script src="{{ asset('js/role-rack.js') }}?v={{ filemtime(public_path('js/role-rack.js')) }}"></script>
@endpush
