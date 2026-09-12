@extends('layouts.master')

@section('title', 'About Us | ZIN-WORKS')
@section('meta-description', 'ZIN-WORKS is a Cambodian job portal built on verified employers. Learn who we are, what we build for job seekers and hiring teams, and how the platform grew.')

@push('styles')
    {{-- jobs.css carries the frontend design tokens plus the shared header/footer
         theming. companies.css is loaded on purpose, not by accident: this page
         reuses the employer directory's hero and company card wholesale, so the
         two stay identical as that design moves. about.css only adds the blocks
         the directory has no equivalent for. --}}
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}?v={{ filemtime(public_path('css/companies.css')) }}">
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

        // Same shape as the directory hero's $heroStats, tones included, so the
        // two pages' stat pills are the same object.
        $heroStats = [
            ['icon' => 'fa-briefcase', 'tone' => 'teal', 'value' => $stats['open_jobs'], 'label' => 'Open ' . Str::plural('role', $stats['open_jobs'])],
            ['icon' => 'fa-users', 'tone' => 'blue', 'value' => $stats['employers'], 'label' => Str::plural('Employer', $stats['employers'])],
            ['icon' => 'fa-shield-halved', 'tone' => 'violet', 'value' => $stats['verified'], 'label' => 'Verified'],
            ['icon' => 'fa-paper-plane', 'tone' => 'gold', 'value' => $stats['applicants'], 'label' => 'Applications'],
        ];

        $pillars = [
            [
                'icon' => 'fa-user-tie',
                'title' => 'For job seekers',
                'lede' => 'Search and apply',
                'text' => 'Search real openings by role, location and work mode. Every advert names a company you can look up, with salary shown up front instead of “negotiable”.',
                'points' => ['Khmer and English throughout', 'Salary on every listing', 'Remote, hybrid and on-site filters'],
                'note' => 'Free to use',
                'link' => ['label' => 'Browse jobs', 'href' => route('jobs.index')],
            ],
            [
                'icon' => 'fa-briefcase',
                'title' => 'For employers',
                'lede' => 'Publish and track',
                'text' => 'Publish a role in minutes and manage the whole pipeline from one back office — drafts, live adverts, deadlines and the company profile candidates read.',
                'points' => ['Draft, publish, close in one place', 'Company profile candidates can read', 'Deadline tracking on every post'],
                'note' => 'Self-serve',
                'link' => ['label' => 'Post a job', 'href' => route('register')],
            ],
            [
                'icon' => 'fa-shield-halved',
                'title' => 'Verification first',
                'lede' => 'Checked, then listed',
                'text' => 'Employers submit their registration and licences to our compliance register. Only what our team has checked earns the blue tick you see beside a company name.',
                'points' => ['Registration and licence checks', 'Expiry dates monitored', 'The blue tick means checked'],
                'note' => 'Team reviewed',
                'link' => ['label' => 'See employers', 'href' => route('companies.index')],
            ],
        ];

        /*
         | The verification pipeline, in the order a company actually moves
         | through it. This is what the blue tick on a listing stands for, so
         | the page says it plainly rather than asking people to take it on
         | trust.
         */
        $verificationSteps = [
            ['icon' => 'fa-file-arrow-up', 'tone' => 'teal', 'title' => 'They submit', 'text' => 'A company uploads its registration certificate and any licence its industry requires.'],
            ['icon' => 'fa-magnifying-glass', 'tone' => 'blue', 'title' => 'We review', 'text' => 'Our compliance team reads each document and checks the details against the company profile.'],
            ['icon' => 'fa-circle-check', 'tone' => 'violet', 'title' => 'The tick appears', 'text' => 'Once checked, the blue tick shows beside that employer everywhere on the site.'],
            ['icon' => 'fa-clock-rotate-left', 'tone' => 'gold', 'title' => 'We keep watching', 'text' => 'Expiry dates stay on file. A lapsed licence loses the tick until it is renewed.'],
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

    {{-- Hero: the directory hero's own classes, so the mint curve, blue sweep,
         orb, dashed arc and floating badges are literally the same design. --}}
    <section class="jf-cdir-hero" aria-labelledby="about-title">
        <span class="jf-cdir-hero__curve" aria-hidden="true"></span>
        <span class="jf-cdir-hero__sweep" aria-hidden="true"></span>

        <div class="jf-shell jf-cdir-hero__inner">
            <div class="jf-cdir-hero__copy">
                <span class="jf-cdir-hero__eyebrow">
                    <span class="jf-cdir-hero__eyebrow-icon" aria-hidden="true">
                        <i class="fas fa-circle-info"></i>
                    </span>
                    About ZIN-WORKS
                </span>

                <h1 id="about-title">
                    The job portal that
                    <span class="jf-cdir-hero__accent">
                        checks first
                        {{-- Hand-drawn underline, so it swings under the descender
                             instead of clipping it the way text-decoration does. --}}
                        <svg class="jf-cdir-hero__underline" viewBox="0 0 220 12" preserveAspectRatio="none"
                            aria-hidden="true" focusable="false">
                            <path d="M3 8.4C48 3.2 122 1.9 217 5.6" fill="none" stroke="currentColor"
                                stroke-width="3.2" stroke-linecap="round" />
                        </svg>
                    </span>
                </h1>

                <p>A Cambodian job platform where every advert names a real company and shows what the role pays — and where the blue tick means our team has read the paperwork.</p>

                <dl class="jf-cdir-hero__stats" aria-label="ZIN-WORKS at a glance">
                    @foreach ($heroStats as $stat)
                        <div class="jf-cdir-stat">
                            <span class="jf-cdir-stat__icon jf-cdir-stat__icon--{{ $stat['tone'] }}" aria-hidden="true">
                                <i class="fas {{ $stat['icon'] }}"></i>
                            </span>
                            <span class="jf-cdir-stat__text">
                                <dt>{{ number_format($stat['value']) }}</dt>
                                <dd>{{ $stat['label'] }}</dd>
                            </span>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Decoration only, so the whole composition is hidden from assistive
                 tech and dropped altogether on narrow screens. --}}
            <div class="jf-cdir-hero__art" aria-hidden="true">
                <span class="jf-cdir-hero__orb"></span>

                <svg class="jf-cdir-hero__arc" viewBox="0 0 320 300" fill="none" focusable="false">
                    <path d="M28 214C6 132 62 34 168 22c74-8 128 34 140 96" stroke="rgba(150, 240, 220, 0.34)"
                        stroke-width="1.4" stroke-dasharray="5 7" />
                    <circle cx="28" cy="214" r="6" fill="#5fe3c2" />
                    <circle cx="303" cy="140" r="5" fill="rgba(150, 240, 220, 0.6)" />
                </svg>

                <span class="jf-cdir-hero__badge jf-cdir-hero__badge--one">
                    <i class="fas fa-shield-halved"></i>
                </span>
                <span class="jf-cdir-hero__badge jf-cdir-hero__badge--two">
                    <i class="fas fa-file-circle-check"></i>
                </span>
                <span class="jf-cdir-hero__badge jf-cdir-hero__badge--three">
                    <i class="fas fa-user-group"></i>
                </span>
            </div>
        </div>
    </section>

    {{-- The slim card that floats over the hero's bottom edge, holding the one
         sentence the rest of the page elaborates. Same seat the directory's
         search bar takes. --}}
    <section class="jf-abt-bar" aria-label="What ZIN-WORKS is">
        <div class="jf-shell jf-abt-bar__card">
            <span class="jf-abt-bar__icon" aria-hidden="true">
                <i class="fas fa-building-shield"></i>
            </span>

            <p class="jf-abt-bar__text">
                <strong>We check the employer, not just the advert.</strong>
                Registration, licence and expiry date are on file before a verified company can post a role.
            </p>

            <a class="jf-cdir-submit jf-abt-bar__cta" href="{{ route('companies.index') }}">
                <i class="fas fa-user-group" aria-hidden="true"></i>
                Meet the employers
            </a>
        </div>
    </section>

    <section class="jf-abt" aria-labelledby="about-pillars-title">
        <div class="jf-shell">
            <div class="jf-cdir__heading">
                <div>
                    <span class="jf-kicker" id="about-pillars-title">What we build</span>
                    <p>The same record serves the candidate reading an advert, the team publishing it, and the officer verifying the company behind it.</p>
                </div>
                <span class="jf-cdir__count">
                    <strong>{{ count($pillars) }}</strong> sides, one platform
                </span>
            </div>

            {{-- The directory's company card, carrying a pillar instead of a
                 company: same cover band, same overlapping chip, same footer. --}}
            <div class="jf-cdir__grid">
                @foreach ($pillars as $pillar)
                    <article class="jf-ccard jf-abt-card">
                        <div class="jf-ccard__cover" aria-hidden="true"></div>

                        <div class="jf-ccard__body">
                            <span class="jf-ccard__logo" aria-hidden="true">
                                <i class="fas {{ $pillar['icon'] }}"></i>
                            </span>

                            <h2 class="jf-ccard__name">{{ $pillar['title'] }}</h2>
                            <p class="jf-ccard__industry">{{ $pillar['lede'] }}</p>
                            <p class="jf-ccard__blurb">{{ $pillar['text'] }}</p>

                            <ul class="jf-abt-card__points">
                                @foreach ($pillar['points'] as $point)
                                    <li><i class="fas fa-check" aria-hidden="true"></i> {{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="jf-ccard__footer">
                            <span class="jf-ccard__roles is-hiring">
                                <strong>{{ $pillar['note'] }}</strong>
                            </span>

                            <a class="jf-ccard__link" href="{{ $pillar['link']['href'] }}">
                                {{ $pillar['link']['label'] }} <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- The verification pipeline spelled out. It is the claim the whole page
         rests on, so it gets its own band rather than a bullet in a card. --}}
    <section class="jf-abt jf-abt--tint" aria-labelledby="about-process-title">
        <div class="jf-shell">
            <div class="jf-cdir__heading">
                <div>
                    <span class="jf-kicker" id="about-process-title">Verification</span>
                    <p>How a blue tick is earned — four steps, every one of them recorded in the compliance register.</p>
                </div>
                <span class="jf-cdir__count">
                    <strong>{{ $stats['verified'] }}</strong> of {{ $stats['employers'] }} {{ Str::plural('employer', $stats['employers']) }} verified
                </span>
            </div>

            <ol class="jf-abt-steps">
                @foreach ($verificationSteps as $index => $step)
                    <li class="jf-abt-step">
                        <span class="jf-abt-step__connector" aria-hidden="true"></span>

                        <span class="jf-abt-step__icon jf-cdir-stat__icon--{{ $step['tone'] }}" aria-hidden="true">
                            <i class="fas {{ $step['icon'] }}"></i>
                        </span>

                        <span class="jf-abt-step__number">Step {{ $index + 1 }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="jf-abt" aria-labelledby="about-timeline-title">
        <div class="jf-shell">
            <div class="jf-cdir__heading">
                <div>
                    <span class="jf-kicker" id="about-timeline-title">Story of success</span>
                    <p>How we got here — from a single job board to a verified hiring platform.</p>
                </div>
                <span class="jf-cdir__count">
                    <strong>{{ count($milestones) }}</strong> milestones
                </span>
            </div>

            <ol class="jf-abt-rail">
                @foreach ($milestones as $index => $milestone)
                    <li class="jf-abt-rail__item{{ $index === count($milestones) - 1 ? ' is-active' : '' }}">
                        <span class="jf-abt-rail__dot" aria-hidden="true"></span>
                        <span class="jf-abt-rail__year">{{ $milestone['year'] }}</span>
                        <p>{{ $milestone['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Real employers, in the directory's own card, linking into the directory
         itself. Every word on screen is read out of the database. --}}
    <section class="jf-abt jf-abt--tint" aria-labelledby="about-employers-title">
        <div class="jf-shell">
            <div class="jf-cdir__heading">
                <div>
                    <span class="jf-kicker" id="about-employers-title">On the platform now</span>
                    <p>Approved employers, busiest first. The blue tick means our team has checked that company's licences.</p>
                </div>
                <span class="jf-cdir__count">
                    <strong>{{ $employers->count() }}</strong> of {{ number_format($stats['employers']) }} shown
                </span>
            </div>

            @if ($employers->isEmpty())
                <div class="jf-cdir-empty">
                    <span class="jf-cdir-empty__icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                    <h2>No approved employers yet</h2>
                    <p>Companies appear here once their profile is approved.</p>
                    <a href="{{ route('companies.index') }}">Open the directory</a>
                </div>
            @else
                <div class="jf-cdir__grid">
                    @foreach ($employers as $employer)
                        @php($openRoles = (int) $employer->open_jobs_count)
                        <article class="jf-ccard">
                            <div @class(['jf-ccard__cover', 'jf-ccard__cover--photo' => $employer->coverUrl()])
                                @if ($employer->coverUrl()) style="background-image: url('{{ $employer->coverUrl() }}')" @endif
                                aria-hidden="true"></div>

                            <div class="jf-ccard__body">
                                <span class="jf-ccard__logo" aria-hidden="true">
                                    @if ($employer->logoUrl())
                                        <img src="{{ $employer->logoUrl() }}" alt="">
                                    @else
                                        {{ $employer->initials() }}
                                    @endif
                                </span>

                                <h2 class="jf-ccard__name">
                                    <a href="{{ route('companies.profile', $employer->slug) }}">{{ $employer->name }}</a>
                                    @if ($employer->hasVerifiedCompliance())
                                        <x-verified-badge :show-label="false" :size="15" label="Verified employer" />
                                    @endif
                                </h2>

                                @if ($employer->industry)
                                    <p class="jf-ccard__industry">{{ $employer->industry }}</p>
                                @endif

                                @if ($employer->description)
                                    <p class="jf-ccard__blurb">{{ Str::limit(strip_tags($employer->description), 130) }}</p>
                                @endif

                                <div class="jf-ccard__chips">
                                    @if ($employer->employer_type)
                                        <span><i class="fas fa-briefcase" aria-hidden="true"></i> {{ $employer->employer_type }}</span>
                                    @endif
                                    @if ($employer->employee_count)
                                        <span><i class="fas fa-users" aria-hidden="true"></i> {{ $employer->employee_count }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="jf-ccard__footer">
                                <span @class(['jf-ccard__roles', 'is-hiring' => $openRoles > 0])>
                                    @if ($openRoles > 0)
                                        <strong>{{ $openRoles }}</strong> open {{ Str::plural('role', $openRoles) }}
                                    @else
                                        No open roles
                                    @endif
                                </span>

                                <a class="jf-ccard__link" href="{{ route('companies.profile', $employer->slug) }}">
                                    View profile <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Closing call to action, built out of the hero's own parts so the page
         ends on the colour it opened with. --}}
    <section class="jf-abt-cta" aria-labelledby="about-cta-title">
        <div class="jf-shell jf-abt-cta__panel">
            <span class="jf-cdir-hero__curve" aria-hidden="true"></span>

            <div class="jf-abt-cta__copy">
                <span class="jf-cdir-hero__eyebrow">
                    <span class="jf-cdir-hero__eyebrow-icon" aria-hidden="true">
                        <i class="fas fa-rocket"></i>
                    </span>
                    Get started
                </span>

                <h2 id="about-cta-title">Whichever side of the table you are on</h2>
                <p>Browse {{ number_format($stats['open_jobs']) }} open {{ Str::plural('role', $stats['open_jobs']) }}, or put your own in front of candidates who can see you have been checked.</p>
            </div>

            <div class="jf-abt-cta__actions">
                <a class="jf-cdir-submit" href="{{ route('jobs.index') }}">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    Browse jobs
                </a>
                <a class="jf-abt-cta__ghost" href="{{ route('register') }}">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    Post a job
                </a>
            </div>
        </div>
    </section>

@endsection
