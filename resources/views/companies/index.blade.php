@extends('layouts.master')

@section('title', 'Companies | ZIN-WORKS')
@section('meta-description', 'Browse verified employers hiring across Cambodia — read their profile, then apply to their open roles.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jobs.css') }}?v={{ filemtime(public_path('css/jobs.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/companies.css') }}?v={{ filemtime(public_path('css/companies.css')) }}">
@endpush

@section('content')
    <section class="jf-cdir-hero" aria-labelledby="companies-title">
        <span class="jf-cdir-hero__glow jf-cdir-hero__glow--one" aria-hidden="true"></span>
        <span class="jf-cdir-hero__glow jf-cdir-hero__glow--two" aria-hidden="true"></span>

        <div class="jf-shell jf-cdir-hero__inner">
            <span class="jf-cdir-hero__eyebrow">
                {{-- A live pulse rather than an icon, matching the landing hero. --}}
                <span class="jf-cdir-hero__dot" aria-hidden="true"></span>
                Employer directory
            </span>

            <h1 id="companies-title">
                Companies <span class="jf-cdir-hero__accent">hiring now</span>
            </h1>

            <p>Read who a team is before you apply to them — every employer here has been approved by ZIN-WORKS.</p>

            <dl class="jf-cdir-hero__stats" aria-label="Directory at a glance">
                <div>
                    <dt>{{ $totalCompanies }}</dt>
                    <dd>{{ Str::plural('Employer', $totalCompanies) }}</dd>
                </div>
                <div>
                    <dt>{{ $totalOpenRoles }}</dt>
                    <dd>Open {{ Str::plural('role', $totalOpenRoles) }}</dd>
                </div>
                <div>
                    <dt>{{ count($industries) }}</dt>
                    <dd>{{ Str::plural('Industry', count($industries)) }}</dd>
                </div>
            </dl>
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
