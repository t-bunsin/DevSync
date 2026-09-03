@extends('layouts.master')

@section('title', $company->name . ' | Companies | ZIN-WORKS')
@section('meta-description', Str::limit(strip_tags($company->description ?: 'Read the ' . $company->name . ' employer profile on ZIN-WORKS and apply to their open roles.'), 155))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}?v={{ filemtime(public_path('css/companies.css')) }}">
@endpush

@section('content')
    @php
        $sections = $company->profileSections();
        $details = $company->employerDetails();
        $socials = $company->socialLinks();
        $openRoles = count($jobs);
    @endphp

    <nav class="jf-cprofile-crumbs" aria-label="Breadcrumb">
        <div class="jf-shell">
            <a href="{{ route('companies.index') }}">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                All companies
            </a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $company->name }}</span>
        </div>
    </nav>

    <header class="jf-cprofile-hero">
        <div @class(['jf-cprofile-hero__cover', 'jf-cprofile-hero__cover--photo' => $company->coverUrl()])
            @if ($company->coverUrl()) style="background-image: url('{{ $company->coverUrl() }}')" @endif
            role="img" aria-label="{{ $company->name }} cover image"></div>

        <div class="jf-shell jf-cprofile-hero__bar">
            <span class="jf-cprofile-hero__logo" aria-hidden="true">
                @if ($company->logoUrl())
                    <img src="{{ $company->logoUrl() }}" alt="">
                @else
                    {{ $company->initials() }}
                @endif
            </span>

            <div class="jf-cprofile-hero__identity">
                <h1>
                    {{ $company->name }}
                    @if ($company->hasVerifiedCompliance())
                        <x-verified-badge :show-label="false" :size="22" label="Verified employer" />
                    @endif
                </h1>

                <div class="jf-cprofile-hero__meta">
                    @if ($company->industry)
                        <span><i class="fas fa-layer-group" aria-hidden="true"></i> {{ $company->industry }}</span>
                    @endif
                    @if ($company->employee_count)
                        <span><i class="fas fa-users" aria-hidden="true"></i> {{ $company->employee_count }} employees</span>
                    @endif
                    @if ($company->address)
                        <span><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $company->address }}</span>
                    @endif
                </div>
            </div>

            <div class="jf-cprofile-hero__actions">
                @if ($openRoles > 0)
                    <a class="jf-btn jf-btn--primary" href="#company-jobs">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        {{ $openRoles }} open {{ Str::plural('role', $openRoles) }}
                    </a>
                @else
                    <span class="jf-cprofile-hero__quiet">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        Not hiring right now
                    </span>
                @endif

                @if ($company->website)
                    <a class="jf-btn jf-btn--ghost" href="{{ $company->website }}" target="_blank" rel="noopener noreferrer">
                        <i class="fas fa-globe" aria-hidden="true"></i>
                        Website
                    </a>
                @endif
            </div>
        </div>
    </header>

    <div class="jf-cprofile-layout jf-shell">
        <div class="jf-cprofile-main">
            @if ($company->description)
                <section class="jf-cprofile-card" aria-labelledby="company-about-title">
                    <h2 id="company-about-title">About {{ $company->name }}</h2>
                    <div class="jf-cprofile-prose">
                        @include('companies.partials.prose', ['body' => $company->description])
                    </div>
                </section>
            @endif

            {{-- The employer-authored sections, in the model's reading order.
                 <details> rather than scripted panels: collapsing is native,
                 keyboard-accessible, and works without JS. --}}
            @foreach ($sections as $index => $section)
                <details class="jf-cprofile-item" @if ($index < 2) open @endif>
                    <summary class="jf-cprofile-item__summary">
                        <span class="jf-cprofile-item__icon jf-cprofile-item__icon--{{ $section['tone'] }}" aria-hidden="true">
                            <i class="fas {{ $section['icon'] }}"></i>
                        </span>
                        <span class="jf-cprofile-item__title">{{ $section['title'] }}</span>
                        <i class="fas fa-chevron-down jf-cprofile-item__chevron" aria-hidden="true"></i>
                    </summary>

                    <div class="jf-cprofile-item__body">
                        @include('companies.partials.prose', ['body' => $section['body']])
                    </div>
                </details>
            @endforeach

            @unless ($company->description || $sections)
                <section class="jf-cprofile-card jf-cprofile-card--muted">
                    <p>{{ $company->name }} has not published a company profile yet. Their open roles are listed below.</p>
                </section>
            @endunless

            <section class="jf-cprofile-jobs" id="company-jobs" aria-labelledby="company-jobs-title">
                <div class="jf-cprofile-jobs__heading">
                    <h2 id="company-jobs-title">Open roles at {{ $company->name }}</h2>
                    <span>{{ $openRoles }} {{ Str::plural('role', $openRoles) }}</span>
                </div>

                @forelse ($jobs as $job)
                    <article class="jf-crole{{ $job['featured'] ? ' is-featured' : '' }}">
                        <div class="jf-crole__main">
                            <h3>
                                <a href="{{ route('jobs.show', $job['id']) }}">{{ $job['title'] }}</a>
                            </h3>
                            <div class="jf-crole__meta">
                                <span><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $job['location'] }}</span>
                                <span><i class="fas fa-briefcase" aria-hidden="true"></i> {{ $job['type'] }}</span>
                                <span><i class="fas fa-building" aria-hidden="true"></i> {{ $job['mode'] }}</span>
                                <span><i class="fas fa-clock" aria-hidden="true"></i> {{ $job['posted'] }}</span>
                            </div>
                        </div>

                        <div class="jf-crole__side">
                            <p class="jf-crole__salary">{{ $job['short_salary'] }}</p>
                            {{-- A plain link, not a scripted button: the gate route sends
                                 guests to register and members straight to the form. --}}
                            <a class="jf-crole__apply" href="{{ route('jobs.apply', $job['id']) }}">
                                Apply <i class="fas fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <p class="jf-cprofile-jobs__empty">
                        No live vacancies at the moment — check the directory for employers who are hiring today.
                    </p>
                @endforelse
            </section>
        </div>

        <aside class="jf-cprofile-side" aria-label="Company details">
            @if ($details)
                <section class="jf-cprofile-panel">
                    <h2>Company details</h2>
                    <dl class="jf-cprofile-facts">
                        @foreach ($details as $label => $value)
                            <div>
                                <dt>{{ $label }}</dt>
                                <dd>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            @if ($company->address || $company->email || $company->phone)
                <section class="jf-cprofile-panel">
                    <h2>Get in touch</h2>
                    <ul class="jf-cprofile-contact">
                        @if ($company->address)
                            <li>
                                <i class="fas fa-location-dot" aria-hidden="true"></i>
                                <span>{{ $company->address }}</span>
                            </li>
                        @endif
                        @if ($company->email)
                            <li>
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                            </li>
                        @endif
                        @if ($company->phone)
                            <li>
                                <i class="fas fa-phone" aria-hidden="true"></i>
                                <a href="tel:{{ preg_replace('/\s+/', '', $company->phone) }}">{{ $company->phone }}</a>
                            </li>
                        @endif
                    </ul>
                </section>
            @endif

            @if ($socials)
                <section class="jf-cprofile-panel">
                    <h2>Follow {{ $company->name }}</h2>
                    <div class="jf-cprofile-social">
                        @foreach ($socials as $link)
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">
                                <i class="{{ $link['icon'] }}" aria-hidden="true"></i>
                                <span>
                                    <strong>{{ $link['label'] }}</strong>
                                    <small>{{ $link['handle'] }}</small>
                                </span>
                                <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($relatedCompanies)
                <section class="jf-cprofile-panel">
                    <h2>Similar employers</h2>
                    <div class="jf-cprofile-related">
                        @foreach ($relatedCompanies as $related)
                            <a href="{{ route('companies.profile', $related->slug) }}">
                                <span class="jf-cprofile-related__logo" aria-hidden="true">
                                    @if ($related->logoUrl())
                                        <img src="{{ $related->logoUrl() }}" alt="">
                                    @else
                                        {{ $related->initials() }}
                                    @endif
                                </span>
                                <span class="jf-cprofile-related__text">
                                    <strong>{{ $related->name }}</strong>
                                    <small>
                                        @if ((int) $related->open_jobs_count > 0)
                                            {{ $related->open_jobs_count }} open {{ Str::plural('role', (int) $related->open_jobs_count) }}
                                        @else
                                            {{ $related->industry ?: 'Employer profile' }}
                                        @endif
                                    </small>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>
@endsection
