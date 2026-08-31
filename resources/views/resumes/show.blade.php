@extends('layouts.admin')

@section('title', $resume->full_name . ' | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $month = fn (?string $value) => \App\Models\Resume::formatMonth($value);
        $contact = array_filter([$resume->email, $resume->phone, $resume->location]);
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('resumes.index') }}">{{ __('ui.bo.resumes.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.resumes.preview_title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.resumes.preview_title') }}</h1>
                <p>{{ __('ui.bo.resumes.preview_subtitle') }}</p>
            </div>

            <div class="kh-bo__actions">
                <a class="kh-bo__btn" href="{{ route('resumes.download', $resume) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v12" /><path d="M7 12l5 5 5-5" /><path d="M4 21h16" />
                    </svg>
                    {{ __('ui.bo.resumes.download_pdf') }}
                </a>
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.edit', $resume) }}">{{ __('ui.bo.edit') }}</a>
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">{{ __('ui.bo.resumes.back_to_register') }}</a>
            </div>
        </header>

        <article class="kh-cv">
            <header class="kh-cv__head">
                <span @class(['kh-cv__monogram', 'kh-cv__monogram--photo' => $resume->photoUrl()]) aria-hidden="true">
                    @if ($resume->photoUrl())
                        <img src="{{ $resume->photoUrl() }}" alt="">
                    @else
                        {{ $resume->initials() }}
                    @endif
                </span>
                <div>
                    <h2 class="kh-cv__name">{{ $resume->full_name }}</h2>
                    @if ($contact)
                        <p class="kh-cv__contact">{{ implode('  |  ', $contact) }}</p>
                    @endif
                </div>
            </header>

            @if ($resume->summary)
                <section class="kh-cv__row">
                    <h3>{{ __('ui.bo.resumes.section_summary') }}</h3>
                    <div><p>{{ $resume->summary }}</p></div>
                </section>
            @endif

            @if ($resume->section('work_history'))
                <section class="kh-cv__row">
                    <h3>{{ __('ui.bo.resumes.section_work') }}</h3>
                    <div>
                        @foreach ($resume->section('work_history') as $job)
                            <div class="kh-cv__entry">
                                <div class="kh-cv__entry-head">
                                    <strong>{{ $job['role'] ?? 'Untitled role' }}</strong>
                                    <span class="kh-cv__dates">
                                        {{ $month($job['started_on'] ?? null) }}
                                        @if (($job['started_on'] ?? null) || ($job['ended_on'] ?? null))
                                            - {{ $month($job['ended_on'] ?? null) ?: 'Present' }}
                                        @endif
                                    </span>
                                </div>
                                <p class="kh-cv__meta">
                                    {{ implode(' | ', array_filter([$job['company'] ?? null, $job['location'] ?? null])) }}
                                </p>
                                @if (! empty($job['bullets']))
                                    <ul>
                                        @foreach ($job['bullets'] as $bullet)
                                            <li>{{ $bullet }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($resume->skillList())
                <section class="kh-cv__row">
                    <h3>{{ __('ui.bo.resumes.section_skills') }}</h3>
                    <div>
                        <ul class="kh-cv__columns">
                            @foreach ($resume->skillList() as $skill)
                                <li>{{ $skill }}</li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if ($resume->section('certifications'))
                <section class="kh-cv__row">
                    <h3>{{ __('ui.bo.resumes.section_certifications') }}</h3>
                    <div>
                        <ul>
                            @foreach ($resume->section('certifications') as $certificate)
                                <li>{{ implode(' - ', array_filter([$certificate['name'] ?? null, $certificate['issuer'] ?? null])) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            @endif

            @if ($resume->section('education'))
                <section class="kh-cv__row">
                    <h3>{{ __('ui.bo.resumes.section_education') }}</h3>
                    <div>
                        @foreach ($resume->section('education') as $study)
                            <div class="kh-cv__entry">
                                <div class="kh-cv__entry-head">
                                    <strong>{{ $study['degree'] ?? 'Qualification' }}</strong>
                                    @if (! empty($study['field']))
                                        <span>: {{ $study['field'] }}</span>
                                    @endif
                                    <span class="kh-cv__dates">{{ $month($study['graduated_on'] ?? null) }}</span>
                                </div>
                                <p class="kh-cv__meta">
                                    {{ implode(' | ', array_filter([$study['institution'] ?? null, $study['location'] ?? null])) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($resume->section('languages'))
                <section class="kh-cv__row">
                    <h3>{{ __('ui.bo.resumes.section_languages') }}</h3>
                    <div class="kh-cv__languages">
                        @foreach ($resume->section('languages') as $language)
                            <div class="kh-cv__language">
                                <strong>{{ $language['name'] ?? '' }}</strong>
                                <span class="kh-cv__bar" aria-hidden="true"></span>
                                <span class="kh-cv__meta">{{ $language['level'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
    </div>
@endsection
