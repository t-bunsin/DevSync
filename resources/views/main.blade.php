@extends('layouts.master')

@section('title', 'ZIN-WORKS | Find Your Next Opportunity')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pricing.css') }}?v={{ filemtime(public_path('css/pricing.css')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
    <link rel="stylesheet" href="{{ asset('css/role-rack.css') }}?v={{ filemtime(public_path('css/role-rack.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/hero-slides.css') }}?v={{ filemtime(public_path('css/hero-slides.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/job-tile.css') }}?v={{ filemtime(public_path('css/job-tile.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
@endpush

@section('content')
    @php
        // Signed-in employers go straight to the post form; everyone else
        // starts with an account, which is where the form would send them.
        $heroUser = auth()->user();
        $postJobHref = $heroUser && ($heroUser->isEmployer() || $heroUser->isAdmin())
            ? route('job-posts.create')
            : route('register');

        $heroSlides = [
            ['id' => 'seek', 'tab' => 'For job seekers', 'icon' => 'fa-magnifying-glass'],
            ['id' => 'hire', 'tab' => 'For employers', 'icon' => 'fa-briefcase'],
        ];

        // Quick searches under the hero search box; each lands on the board
        // with the query already filled in.
        $popularSearches = ['Remote', 'Full-time', 'Design', 'Engineering', 'Banking'];

        $howItWorks = [
            'seek' => [
                'title' => 'For job seekers',
                'icon' => 'fa-magnifying-glass',
                'steps' => [
                    ['title' => 'Search freely', 'body' => 'Browse every verified role. No account needed to look around.'],
                    ['title' => 'Build your profile once', 'body' => 'A two-minute profile, with your CV attached to every application.'],
                    ['title' => 'Apply in one click', 'body' => 'Send applications in seconds and hear back within days.'],
                ],
                'cta' => ['label' => 'Browse jobs', 'href' => route('jobs.index')],
            ],
            'hire' => [
                'title' => 'For employers',
                'icon' => 'fa-briefcase',
                'steps' => [
                    ['title' => 'Create your company page', 'body' => 'Show candidates who you are with a verified employer profile.'],
                    ['title' => 'Post a role in minutes', 'body' => 'One simple form, and your job is live on the board straight away.'],
                    ['title' => 'Review and hire', 'body' => 'Shortlist applicants and move them to hired on one board.'],
                ],
                'cta' => ['label' => 'Post a job', 'href' => $postJobHref],
            ],
        ];
    @endphp

    {{-- Two slides, one per audience, cycled by hero-slides.js. Without the
         script only the first slide shows and the switcher stays hidden. --}}
    <section class="jf-hero jf-hero--rack jf-hero--slides" aria-roledescription="carousel" aria-label="Get started on ZIN-WORKS"
        data-hero-slides data-active-slide="seek">
        <div class="jf-hero__glow jf-hero__glow--one" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--two" aria-hidden="true"></div>
        <div class="jf-hero__glow jf-hero__glow--three" aria-hidden="true"></div>
        <div class="jf-hero__tint" aria-hidden="true"></div>
        <div class="jf-hero__beam" aria-hidden="true"></div>

        <div class="jf-shell">
            <div class="jf-slides__bar" data-slides-bar hidden>
                <div class="jf-slides__tabs" role="tablist" aria-label="Choose your path">
                    @foreach ($heroSlides as $index => $slide)
                        <button type="button" class="jf-slides__tab" role="tab" id="hero-tab-{{ $slide['id'] }}"
                            aria-controls="hero-slide-{{ $slide['id'] }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            tabindex="{{ $index === 0 ? '0' : '-1' }}" data-slide-tab="{{ $slide['id'] }}">
                            <i class="fas {{ $slide['icon'] }}" aria-hidden="true"></i>
                            <span>{{ $slide['tab'] }}</span>
                            <span class="jf-slides__progress" aria-hidden="true"><span data-slide-progress></span></span>
                        </button>
                    @endforeach
                </div>

                <div class="jf-slides__controls">
                    <button type="button" class="jf-slides__btn" data-slide-toggle aria-label="Pause slideshow">
                        <i class="fas fa-pause" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="jf-slides" data-slides>
                {{-- Slide 1: job seekers. Search is the one job here. --}}
                <div class="jf-slide is-active" id="hero-slide-seek" role="tabpanel" aria-roledescription="slide"
                    aria-labelledby="hero-tab-seek" data-slide="seek">
                    <div class="jf-hero__inner">
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
                            <p>Verified jobs from trusted teams in Cambodia and remote-first companies across Southeast Asia.</p>

                            {{-- A plain GET form: the board reads q and location
                                 off the URL and applies them as filters. --}}
                            <form class="jf-hero-search" action="{{ route('jobs.index') }}" method="get" role="search" data-hero-search>
                                <label class="jf-hero-search__field">
                                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                                    <span class="visually-hidden">Job title, skill or company</span>
                                    <input type="search" name="q" placeholder="Job title, skill or company" autocomplete="off">
                                </label>
                                <label class="jf-hero-search__field jf-hero-search__field--select">
                                    <i class="fas fa-location-dot" aria-hidden="true"></i>
                                    <span class="visually-hidden">Location</span>
                                    <select name="location">
                                        <option value="">Anywhere</option>
                                        @foreach (\App\Models\JobPost::locationOptions() as $jobLocation)
                                            <option value="{{ $jobLocation }}">{{ $jobLocation }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button type="submit" class="jf-hero-search__submit">
                                    <span>Search</span>
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </button>
                            </form>

                            <p class="jf-hero__popular">
                                <span>Popular:</span>
                                @foreach ($popularSearches as $term)
                                    <a href="{{ route('jobs.index', ['q' => $term]) }}">{{ $term }}</a>
                                @endforeach
                            </p>
                        </div>

                        <div class="jf-hero__stage">
                            {{-- The roles hang off a rail here; role-rack.js swings them. --}}
                            @include('partials.role-rack')
                        </div>
                    </div>
                </div>

                {{-- Slide 2: employers. Same frame, the hiring side of it. --}}
                <div class="jf-slide jf-slide--hire" id="hero-slide-hire" role="tabpanel" aria-roledescription="slide"
                    aria-labelledby="hero-tab-hire" data-slide="hire" inert>
                    <div class="jf-hero__inner">
                        <div class="jf-hero__copy">
                            <span class="jf-hero__eyebrow">
                                <span class="jf-hero__eyebrow-dot" aria-hidden="true"></span>
                                For employers &amp; hiring teams
                            </span>
                            <h2 class="jf-hero__title">
                                {{-- Breaks set by hand: these words are narrow enough to fit two
                                     lines, and the first slide's headline runs to three. --}}
                                <span class="jf-hero__word">Hire</span>
                                <span class="jf-hero__word">talent</span><br>
                                <span class="jf-hero__word">that</span>
                                <span class="jf-hero__word">fits</span><br>
                                <span class="jf-hero__accent">your team.</span>
                            </h2>
                            <p>Publish a role in minutes, reach verified candidates across Cambodia, and hire from one simple board.</p>

                            <div class="jf-hero__actions">
                                <a class="jf-btn jf-btn--accent" href="{{ $postJobHref }}">
                                    Post a job <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </a>
                                <a class="jf-btn jf-btn--hero-ghost" href="#pricing">See pricing</a>
                            </div>

                            <ul class="jf-hero__checks">
                                <li><i class="fas fa-check" aria-hidden="true"></i> Free to start</li>
                                <li><i class="fas fa-check" aria-hidden="true"></i> Verified candidates</li>
                                <li><i class="fas fa-check" aria-hidden="true"></i> Cancel any time</li>
                            </ul>
                        </div>

                        <div class="jf-hero__stage">
                            @include('partials.hero-hire')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- A plain grid of cards here: the landing page has no detail panel, so
         every card links straight through to its job page. --}}
    @include('jobs.partials.catalog', ['interactive' => false])

    <section class="jf-how" id="how-it-works" aria-labelledby="how-title">
        <div class="jf-shell">
            <div class="jf-section-head">
                <span class="jf-kicker">How it works</span>
                <h2 id="how-title">Simple for both sides of the hire</h2>
                <p>Three steps whether you are looking for your next role or your next teammate.</p>
            </div>

            <div class="jf-how__grid">
                @foreach ($howItWorks as $key => $path)
                    <article class="jf-how__card jf-how__card--{{ $key }}" aria-labelledby="how-{{ $key }}">
                        <div class="jf-how__top">
                            <span class="jf-how__icon" aria-hidden="true"><i class="fas {{ $path['icon'] }}"></i></span>
                            <h3 id="how-{{ $key }}">{{ $path['title'] }}</h3>
                        </div>

                        <ol class="jf-how__steps">
                            @foreach ($path['steps'] as $step)
                                <li>
                                    <strong>{{ $step['title'] }}</strong>
                                    <span>{{ $step['body'] }}</span>
                                </li>
                            @endforeach
                        </ol>

                        <a class="jf-how__cta" href="{{ $path['cta']['href'] }}">
                            {{ $path['cta']['label'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

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

    <section class="jf-cta" aria-labelledby="cta-title">
        <div class="jf-shell">
            <div class="jf-cta__panel">
                <div class="jf-cta__copy">
                    <h2 id="cta-title">Your next move starts here.</h2>
                    <p>Find the role that fits you, or the person who fits the role. Getting started is free.</p>
                </div>
                <div class="jf-cta__actions">
                    <a class="jf-btn jf-btn--accent" href="{{ route('jobs.index') }}">
                        Find a job <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a class="jf-btn jf-btn--hero-ghost" href="{{ $postJobHref }}">Post a job</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/jobs.js') }}?v={{ filemtime(public_path('js/jobs.js')) }}"></script>
    <script src="{{ asset('js/pricing.js') }}?v={{ filemtime(public_path('js/pricing.js')) }}"></script>
    <script src="{{ asset('js/role-rack.js') }}?v={{ filemtime(public_path('js/role-rack.js')) }}"></script>
    <script src="{{ asset('js/hero-slides.js') }}?v={{ filemtime(public_path('js/hero-slides.js')) }}"></script>
@endpush
