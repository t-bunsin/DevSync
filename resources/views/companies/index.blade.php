@extends('layouts.master')

@section('title', 'Companies | ZIN-WORKS')
@section('meta-description', 'Browse verified employers hiring across Cambodia — read their profile, then apply to their open roles.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}?v={{ filemtime(public_path('css/companies.css')) }}">
@endpush

@section('content')
    @php
        // Optional. Drop a cut-out team photo (transparent PNG or WebP) at this
        // path and it fills the orb; without one the orb stays a clean gradient
        // and the composition still reads as finished.
        $heroPhoto = collect(['img/companies-hero.png', 'img/companies-hero.webp'])
            ->first(fn (string $path) => file_exists(public_path($path)));

        $heroStats = [
            ['icon' => 'fa-users', 'tone' => 'teal', 'value' => $totalCompanies, 'label' => Str::plural('Employer', $totalCompanies)],
            ['icon' => 'fa-briefcase', 'tone' => 'blue', 'value' => $totalOpenRoles, 'label' => 'Open ' . Str::plural('role', $totalOpenRoles)],
            ['icon' => 'fa-industry', 'tone' => 'violet', 'value' => count($industries), 'label' => Str::plural('Industry', count($industries))],
        ];
    @endphp

    <section class="jf-cdir-hero" aria-labelledby="companies-title">
        <span class="jf-cdir-hero__curve" aria-hidden="true"></span>
        <span class="jf-cdir-hero__sweep" aria-hidden="true"></span>

        <div class="jf-shell jf-cdir-hero__inner">
            <div class="jf-cdir-hero__copy">
                <span class="jf-cdir-hero__eyebrow">
                    <span class="jf-cdir-hero__eyebrow-icon" aria-hidden="true">
                        <i class="fas fa-user-group"></i>
                    </span>
                    Employer directory
                </span>

                <h1 id="companies-title">
                    Companies
                    <span class="jf-cdir-hero__accent">
                        hiring now
                        {{-- Hand-drawn underline, so it swings under the descender
                             of "g" instead of clipping it the way text-decoration does. --}}
                        <svg class="jf-cdir-hero__underline" viewBox="0 0 220 12" preserveAspectRatio="none"
                            aria-hidden="true" focusable="false">
                            <path d="M3 8.4C48 3.2 122 1.9 217 5.6" fill="none" stroke="currentColor"
                                stroke-width="3.2" stroke-linecap="round" />
                        </svg>
                    </span>
                </h1>

                <p>Read who a team is before you apply to them — every employer here has been approved by ZIN-WORKS.</p>

                <dl class="jf-cdir-hero__stats" aria-label="Directory at a glance">
                    @foreach ($heroStats as $stat)
                        <div class="jf-cdir-stat">
                            <span class="jf-cdir-stat__icon jf-cdir-stat__icon--{{ $stat['tone'] }}" aria-hidden="true">
                                <i class="fas {{ $stat['icon'] }}"></i>
                            </span>
                            <span class="jf-cdir-stat__text">
                                <dt>{{ $stat['value'] }}</dt>
                                <dd>{{ $stat['label'] }}</dd>
                            </span>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Decoration only, so the whole composition is hidden from assistive
                 tech and dropped altogether on narrow screens. --}}
            <div class="jf-cdir-hero__art" aria-hidden="true">
                <span @class(['jf-cdir-hero__orb', 'jf-cdir-hero__orb--photo' => $heroPhoto])>
                    @if ($heroPhoto)
                        <img src="{{ asset($heroPhoto) }}?v={{ filemtime(public_path($heroPhoto)) }}" alt="">
                    @endif
                </span>

                <svg class="jf-cdir-hero__arc" viewBox="0 0 320 300" fill="none" focusable="false">
                    <path d="M28 214C6 132 62 34 168 22c74-8 128 34 140 96" stroke="rgba(150, 240, 220, 0.34)"
                        stroke-width="1.4" stroke-dasharray="5 7" />
                    <circle cx="28" cy="214" r="6" fill="#5fe3c2" />
                    <circle cx="303" cy="140" r="5" fill="rgba(150, 240, 220, 0.6)" />
                </svg>

                <span class="jf-cdir-hero__badge jf-cdir-hero__badge--one">
                    <i class="fas fa-user-group"></i>
                </span>
                <span class="jf-cdir-hero__badge jf-cdir-hero__badge--two">
                    <i class="fas fa-briefcase"></i>
                </span>
                <span class="jf-cdir-hero__badge jf-cdir-hero__badge--three">
                    <i class="fas fa-arrow-trend-up"></i>
                </span>
            </div>
        </div>
    </section>

    {{-- A plain GET form, filtered on the server: the directory is two fields
         and a paginated grid, so there is nothing here worth scripting. The card
         overlaps the hero above it, which is what seats the two together. --}}
    <section class="jf-cdir-filters" aria-label="Filter companies">
        <form class="jf-shell jf-cdir-filters__form" method="GET" action="{{ route('companies.index') }}" role="search">
            <label class="jf-cdir-field jf-cdir-field--wide">
                <span class="visually-hidden">Company name</span>
                <span class="jf-cdir-control">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ $searchTerm }}"
                        placeholder="Search employers by name" autocomplete="off">
                </span>
            </label>

            <label class="jf-cdir-field">
                <span class="visually-hidden">Industry</span>
                <span class="jf-cdir-control jf-cdir-control--select">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    <select name="industry" aria-label="Industry">
                        <option value="">All industries</option>
                        @foreach ($industries as $industry)
                            <option value="{{ $industry }}" @selected($activeIndustry === $industry)>{{ $industry }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down jf-cdir-chevron" aria-hidden="true"></i>
                </span>
            </label>

            <button class="jf-cdir-submit" type="submit">
                <i class="fas fa-search" aria-hidden="true"></i>
                Search
            </button>

            @if ($searchTerm !== '' || $activeIndustry)
                <a class="jf-cdir-clear" href="{{ route('companies.index') }}">
                    <i class="fas fa-rotate-left" aria-hidden="true"></i>
                    Clear
                </a>
            @endif
        </form>
    </section>

    <section class="jf-cdir" aria-labelledby="companies-results-title">
        <div class="jf-shell">
            <div class="jf-cdir__heading">
                <div>
                    {{-- Not "Employer directory" again — the hero eyebrow above
                         already says that. --}}
                    <span class="jf-kicker" id="companies-results-title">All employers</span>
                    <p>Open a profile to read what a company does, then jump straight to its live roles.</p>
                </div>
                <span class="jf-cdir__count">
                    <strong>{{ $companies->total() }}</strong> {{ Str::plural('company', $companies->total()) }} found
                </span>
            </div>

            @if ($companies->isEmpty())
                <div class="jf-cdir-empty">
                    <span class="jf-cdir-empty__icon"><i class="fas fa-building" aria-hidden="true"></i></span>
                    <h2>No companies match that search</h2>
                    <p>Try a shorter name, or clear the industry filter to see every approved employer.</p>
                    <a href="{{ route('companies.index') }}">Show all companies</a>
                </div>
            @else
                <div class="jf-cdir__grid">
                    @foreach ($companies as $company)
                        @php($openRoles = (int) $company->open_jobs_count)
                        <article class="jf-ccard">
                            <div @class(['jf-ccard__cover', 'jf-ccard__cover--photo' => $company->coverUrl()])
                                @if ($company->coverUrl()) style="background-image: url('{{ $company->coverUrl() }}')" @endif
                                aria-hidden="true"></div>

                            <div class="jf-ccard__body">
                                <span class="jf-ccard__logo" aria-hidden="true">
                                    @if ($company->logoUrl())
                                        <img src="{{ $company->logoUrl() }}" alt="">
                                    @else
                                        {{ $company->initials() }}
                                    @endif
                                </span>

                                <h2 class="jf-ccard__name">
                                    <a href="{{ route('companies.profile', $company->slug) }}">{{ $company->name }}</a>
                                    @if ($company->hasVerifiedCompliance())
                                        <x-verified-badge :show-label="false" :size="15" label="Verified employer" />
                                    @endif
                                </h2>

                                @if ($company->industry)
                                    <p class="jf-ccard__industry">{{ $company->industry }}</p>
                                @endif

                                @if ($company->description)
                                    <p class="jf-ccard__blurb">{{ Str::limit(strip_tags($company->description), 130) }}</p>
                                @endif

                                <div class="jf-ccard__chips">
                                    @if ($company->employer_type)
                                        <span><i class="fas fa-briefcase" aria-hidden="true"></i> {{ $company->employer_type }}</span>
                                    @endif
                                    @if ($company->employee_count)
                                        <span><i class="fas fa-users" aria-hidden="true"></i> {{ $company->employee_count }}</span>
                                    @endif
                                </div>

                                @if ($company->address)
                                    <p class="jf-ccard__address">
                                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                                        {{ $company->address }}
                                    </p>
                                @endif
                            </div>

                            <div class="jf-ccard__footer">
                                <span @class(['jf-ccard__roles', 'is-hiring' => $openRoles > 0])>
                                    @if ($openRoles > 0)
                                        <strong>{{ $openRoles }}</strong> open {{ Str::plural('role', $openRoles) }}
                                    @else
                                        No open roles
                                    @endif
                                </span>

                                <a class="jf-ccard__link" href="{{ route('companies.profile', $company->slug) }}">
                                    View profile <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="jf-cdir__pagination">
                    {{ $companies->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
