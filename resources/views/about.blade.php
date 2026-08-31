@extends('layouts.master')

@section('title', 'About Us | ZIN-WORKS')
@section('meta-description', 'ZIN-WORKS is a Cambodian job portal built on verified employers. Learn who we are, what we build for job seekers and hiring teams, and how the platform grew.')

@push('styles')
    {{-- jobs.css carries the frontend design tokens plus the shared header/footer theming. --}}
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/about.css') }}?v={{ filemtime(public_path('css/about.css')) }}">
@endpush

@section('content')
    @php
        /*
         | Page copy lives here, the way the contact page keeps its channels and
         | topics: one block to edit, no hunting through the markup.
         |
         | NOTE: `$milestones` is placeholder history — replace the years and
         | sentences with the real ones before this page goes live.
         */
        $statCards = [
            ['value' => $stats['open_jobs'], 'label' => 'Open jobs', 'hint' => 'Published and accepting applications'],
            ['value' => $stats['employers'], 'label' => 'Employers hiring', 'hint' => 'Approved company profiles'],
            ['value' => $stats['verified'], 'label' => 'Verified employers', 'hint' => 'Licences checked by our team'],
            ['value' => $stats['applicants'], 'label' => 'Applications tracked', 'hint' => 'Across every live role'],
        ];

        $pillars = [
            [
                'icon' => 'fa-user-tie',
                'title' => 'For job seekers',
                'text' => 'Search real openings by role, location and work mode. Every advert names a company you can look up, with salary shown up front instead of “negotiable”.',
                'points' => ['Khmer and English throughout', 'Salary on every listing', 'Remote, hybrid and on-site filters'],
            ],
            [
                'icon' => 'fa-briefcase',
                'title' => 'For employers',
                'text' => 'Publish a role in minutes and manage the whole pipeline from one back office — drafts, live adverts, deadlines and the company profile candidates read.',
                'points' => ['Draft, publish, close in one place', 'Company profile candidates can read', 'Deadline tracking on every post'],
            ],
            [
                'icon' => 'fa-shield-halved',
                'title' => 'Verification first',
                'text' => 'Employers submit their registration and licences to our compliance register. Only what our team has checked earns the blue tick you see beside a company name.',
                'points' => ['Registration and licence checks', 'Expiry dates monitored', 'The blue tick means checked'],
            ],
        ];

        $milestones = [
            ['year' => '2015', 'text' => 'ZIN-WORKS starts in Phnom Penh as a small job board for local employers.'],
            ['year' => '2018', 'text' => 'Employer verification introduced — licences checked before a company can post.'],
            ['year' => '2020', 'text' => 'The platform goes bilingual, Khmer and English, end to end.'],
            ['year' => '2022', 'text' => 'Company profiles launch so candidates can research an employer in one place.'],
            ['year' => '2024', 'text' => 'Remote and hybrid roles become first-class filters across the job explorer.'],
            ['year' => '2026', 'text' => 'The compliance register opens, putting every verification on the record.'],
        ];
    @endphp

    <section class="jf-about-head" aria-labelledby="about-title">
        <div class="jf-shell">
            {{-- Same breadcrumb pattern the job page uses. --}}
            <nav class="jf-about-head__breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <span aria-current="page">About Us</span>
            </nav>

            <h1 id="about-title">About ZIN-WORKS</h1>
            <p>A Cambodian job portal built on employers we have actually checked.</p>
        </div>
    </section>

    <section class="jf-about-intro" aria-labelledby="about-intro-title">
        <div class="jf-shell jf-about-intro__grid">
            <div class="jf-about-intro__copy">
                <span class="jf-kicker">Who we are</span>
                <h2 id="about-intro-title">A job portal built around verified employers</h2>

                <p>
                    <strong>ZIN-WORKS</strong> is a Cambodian job platform that matches candidates with
                    employers whose registration and licences have been checked. Every advert names a real
                    company, shows what the role pays, and says when applications close.
                </p>
                <p>
                    Behind the public pages sits a full recruitment back office. Hiring teams write and
                    publish adverts, keep a company profile candidates can read, and track deadlines and
                    applicant numbers without leaving the platform.
                </p>
                <p>
                    We also keep the paperwork honest. The compliance register records which licence was
                    checked, by whom and until when — that record is what puts the blue tick beside a
                    company name anywhere on the site.
                </p>

                <a class="jf-about-intro__link" href="{{ route('jobs.index') }}">
                    See who is hiring today <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            {{-- A composed mosaic rather than stock photography: it stays on-brand
                 in both themes and needs no assets. Swap a tile for a photo by
                 dropping an <img> inside it. --}}
            <div class="jf-about-mosaic" aria-hidden="true">
                <article class="jf-about-mosaic__tile jf-about-mosaic__tile--verify">
                    <x-verified-badge :show-label="false" :size="30" />
                    <strong>Checked before they post</strong>
                    <span>Registration, licence and expiry date on file for every verified employer.</span>
                </article>

                <article class="jf-about-mosaic__tile jf-about-mosaic__tile--card">
                    <span class="jf-about-mosaic__company">PPCB Bank</span>
                    <strong>Retail Associates</strong>
                    <span class="jf-about-mosaic__meta"><i class="fas fa-location-dot"></i> Phnom Penh · Full-time</span>
                    <span class="jf-about-mosaic__salary">$19.25 – $20.25 / hour</span>
                </article>

                <article class="jf-about-mosaic__tile jf-about-mosaic__tile--stat">
                    <strong>{{ number_format($stats['open_jobs']) }}</strong>
                    <span>roles open right now</span>
                </article>

                <article class="jf-about-mosaic__tile jf-about-mosaic__tile--quote">
                    <i class="fas fa-quote-left"></i>
                    <p>Know who you are applying to before you send anything.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="jf-about-stats" aria-label="ZIN-WORKS in numbers">
        <div class="jf-shell">
            <dl class="jf-about-stats__grid">
                @foreach ($statCards as $card)
                    <div class="jf-about-stats__item">
                        <dt>{{ number_format($card['value']) }}</dt>
                        <dd>
                            {{ $card['label'] }}
                            <span>{{ $card['hint'] }}</span>
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section class="jf-about-pillars" aria-labelledby="about-pillars-title">
        <div class="jf-shell">
            <div class="jf-about-section-head">
                <span class="jf-kicker">What we build</span>
                <h2 id="about-pillars-title">Three jobs, one platform</h2>
                <p>The same record serves the candidate reading an advert, the team publishing it, and the officer verifying the company behind it.</p>
            </div>

            <div class="jf-about-pillars__grid">
                @foreach ($pillars as $pillar)
                    <article class="jf-about-pillar">
                        <span class="jf-about-pillar__icon" aria-hidden="true">
                            <i class="fas {{ $pillar['icon'] }}"></i>
                        </span>
                        <h3>{{ $pillar['title'] }}</h3>
                        <p>{{ $pillar['text'] }}</p>
                        <ul>
                            @foreach ($pillar['points'] as $point)
                                <li><i class="fas fa-check" aria-hidden="true"></i> {{ $point }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="jf-about-timeline" aria-labelledby="about-timeline-title">
        <div class="jf-shell">
            <div class="jf-about-section-head jf-about-section-head--center">
                <span class="jf-kicker">Story of success</span>
                <h2 id="about-timeline-title">How we got here</h2>
                <p>From a single job board to a verified hiring platform.</p>
            </div>

            <ol class="jf-about-timeline__rail">
                @foreach ($milestones as $index => $milestone)
                    <li class="jf-about-timeline__item{{ $index === 0 ? ' is-active' : '' }}">
                        <div class="jf-about-timeline__content">
                            <span class="jf-about-timeline__year">{{ $milestone['year'] }}</span>
                            <p>{{ $milestone['text'] }}</p>
                        </div>
                        <span class="jf-about-timeline__dot" aria-hidden="true"></span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Replaces the old "values" block: same slot, but every word on screen is
         read out of the database rather than asserted. --}}
    <section class="jf-about-employers" aria-labelledby="about-employers-title">
        <div class="jf-shell">
            <div class="jf-about-section-head">
                <span class="jf-kicker">On the platform now</span>
                <h2 id="about-employers-title">Who is hiring on ZIN-WORKS</h2>
                <p>Approved employers, busiest first. The blue tick means our team has checked that company's licences.</p>
            </div>

            @if ($employers->isEmpty())
                <p class="jf-about-empty">No approved employers yet. Companies appear here once their profile is approved.</p>
            @else
                <div class="jf-about-employers__grid">
                    @foreach ($employers as $employer)
                        <article class="jf-about-employer">
                            <span class="jf-about-employer__logo" aria-hidden="true">
                                @if ($employer->logoUrl())
                                    <img src="{{ $employer->logoUrl() }}" alt="">
                                @else
                                    {{ $employer->initials() }}
                                @endif
                            </span>

                            <div class="jf-about-employer__body">
                                <h3>
                                    {{ $employer->name }}
                                    @if ($employer->hasVerifiedCompliance())
                                        <x-verified-badge :show-label="false" :size="15" label="Verified employer" />
                                    @endif
                                </h3>
                                <p>{{ $employer->industry ?: $employer->employer_type ?: 'Employer' }}</p>
                            </div>

                            <span class="jf-about-employer__count">
                                <strong>{{ $employer->open_jobs_count }}</strong>
                                {{ \Illuminate\Support\Str::plural('role', $employer->open_jobs_count) }}
                            </span>
                        </article>
                    @endforeach
                </div>
            @endif

        </div>
    </section>

@endsection
