@extends('layouts.admin')

@section('title', $application->full_name . ' | ' . ($post?->title ?? 'Application'))

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $appliedAt = $application->applied_at ?? $application->created_at;
        $decidedAt = $application->decidedAt();
        $tones = \App\Models\JobApplication::statusTones();
        $canDownloadCv = auth()->user()?->hasPermission(\App\Models\Permission::APPLICATION_DOWNLOAD);
        $canDownloadResume = auth()->user()?->hasPermission(\App\Models\Permission::RESUME_DOWNLOAD);
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.index') }}">{{ __('ui.bo.job_posts.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.applications', $post) }}">{{ $post->title }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $application->full_name }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ $application->full_name }}</h1>
                <p>
                    {{ __('ui.bo.applications.applied_for') }} <a href="{{ route('job-posts.show', $post) }}">{{ $post->title }}</a>
                    · {{ $application->appliedAgo() }}
                    @if ($appliedAt)
                        · {{ $appliedAt->format('d M Y, H:i') }}
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.applications', $post) }}">{{ __('ui.bo.applications.back_to_candidates') }}</a>

                <a class="kh-bo__btn kh-bo__btn--ghost"
                    href="mailto:{{ $application->email }}?subject={{ rawurlencode('Your application for ' . $post->title) }}">{{ __('ui.bo.applications.email_them') }}</a>

                <form method="POST" action="{{ route('job-applications.destroy', $application) }}"
                    onsubmit="return confirm('Delete the application from {{ addslashes($application->full_name) }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button class="kh-bo__btn kh-bo__btn--ghost kh-bo__btn--danger" type="submit">{{ __('ui.bo.delete') }}</button>
                </form>
            </div>
        </header>

        @if (session('success'))
            <div class="kh-bo__flash" role="status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="kh-bo__detail">
            <div class="kh-bo__detail-main">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.applications.review') }}</h2>
                            <p>{{ __('ui.bo.applications.review_hint') }}</p>
                        </div>

                        <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                            {{ __('ui.bo.status.' . $application->status) }}
                        </span>
                    </div>

                    <div class="kh-bo__card-body">
                        <form class="kh-bo__stackform" method="POST" action="{{ route('job-applications.update', $application) }}">
                            @csrf
                            @method('PATCH')

                            {{-- Radios, not a select: the five states are the whole
                                 pipeline, and seeing where this candidate sits in it
                                 is the point of the page. --}}
                            <fieldset class="kh-bo__pills">
                                <legend class="kh-bo__label">{{ __('ui.bo.applications.col_status') }}</legend>

                                @foreach (\App\Models\JobApplication::statuses() as $status)
                                    <label class="kh-bo__pill kh-bo__pill--{{ $tones[$status] ?? 'neutral' }}">
                                        <input type="radio" name="status" value="{{ $status }}"
                                            @checked($application->status === $status)>
                                        <span>{{ ucfirst($status) }}</span>
                                    </label>
                                @endforeach
                            </fieldset>

                            @if ($decidedAt)
                                <p class="kh-bo__hint">
                                    {{ $application->decisionLabel() }} {{ $decidedAt->format('d M Y, H:i') }}
                                    ({{ $decidedAt->diffForHumans() }}).
                                </p>
                            @endif

                            <label class="kh-bo__field">
                                <span class="kh-bo__label">{{ __('ui.bo.applications.internal_note') }}</span>
                                <textarea class="kh-bo__control" name="note" rows="4"
                                    placeholder="{{ __('ui.bo.applications.internal_note_placeholder') }}">{{ $application->note }}</textarea>
                                <small class="kh-bo__hint">{{ __('ui.bo.applications.internal_note_hint') }}</small>
                            </label>

                            <label class="kh-bo__field">
                                <span class="kh-bo__label">{{ __('ui.bo.applications.message_to_candidate') }}</span>
                                <textarea class="kh-bo__control" name="candidate_message" rows="4"
                                    placeholder="{{ __('ui.bo.applications.message_placeholder', ['name' => $application->full_name]) }}">{{ $application->candidate_message }}</textarea>
                                <small class="kh-bo__hint kh-bo__hint--warn">
                                    {{ $application->full_name }} reads this on their application page. Leave it empty to say nothing.
                                </small>
                            </label>

                            <div class="kh-bo__form-actions">
                                <button class="kh-bo__btn" type="submit">{{ __('ui.bo.applications.save_review') }}</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.applications.their_message') }}</h2>
                            <p>{{ __('ui.bo.applications.their_message_hint') }}</p>
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        @if ($application->message)
                            <blockquote class="kh-bo__quote">{{ $application->message }}</blockquote>
                        @else
                            <p class="kh-bo__muted">{{ __('ui.bo.applications.no_message') }}</p>
                        @endif
                    </div>
                </section>
            </div>

            <aside class="kh-bo__detail-side">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.applications.candidate') }}</h2>
                            <p>{{ __('ui.bo.applications.reach_them') }}</p>
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        <div class="kh-bo__idhead">
                            <span class="kh-bo__idhead-avatar" aria-hidden="true">
                                @if ($application->photoUrl())
                                    <img src="{{ $application->photoUrl() }}" alt="">
                                @else
                                    {{ $application->initials() }}
                                @endif
                            </span>

                            <div class="kh-bo__idhead-body">
                                <strong>{{ $application->full_name }}</strong>
                                <a href="mailto:{{ $application->email }}?subject={{ rawurlencode('Your application for ' . $post->title) }}">{{ $application->email }}</a>
                            </div>
                        </div>

                        <ul class="kh-bo__speclist">
                            <li class="kh-bo__specrow">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.2a2 2 0 012.1-.5c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z" />
                                </svg>
                                <div class="kh-bo__specrow-body">
                                    <span class="kh-bo__specrow-label">{{ __('ui.bo.applications.phone') }}</span>
                                    <span class="kh-bo__specrow-value">
                                        @if ($application->phone)
                                            <a href="tel:{{ $application->phone }}">{{ $application->phone }}</a>
                                        @else
                                            Not given
                                        @endif
                                    </span>
                                </div>
                            </li>

                            <li class="kh-bo__specrow">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                                </svg>
                                <div class="kh-bo__specrow-body">
                                    <span class="kh-bo__specrow-label">CV</span>

                                    {{-- An uploaded file follows application.download; the
                                         resume fallback follows resume.download. --}}
                                    @if ($application->cv_path)
                                        @if ($canDownloadCv)
                                            <a class="kh-bo__filechip" href="{{ route('job-applications.cv', $application) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" />
                                                </svg>
                                                {{ $application->cvLabel() }}
                                            </a>
                                        @else
                                            <span class="kh-bo__filechip">{{ $application->cvLabel() }}</span>
                                        @endif
                                    @elseif ($application->resume)
                                        @if ($canDownloadResume)
                                            <a class="kh-bo__filechip" href="{{ route('resumes.download', $application->resume) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" /><path d="M7 10l5 5 5-5" /><path d="M12 15V3" />
                                                </svg>
                                                {{ $application->cvLabel() }}
                                            </a>
                                        @else
                                            <span class="kh-bo__filechip">{{ $application->cvLabel() }}</span>
                                        @endif
                                    @else
                                        <span class="kh-bo__specrow-value">{{ __('ui.bo.applications.no_cv') }}</span>
                                    @endif

                                    @if ($application->resume)
                                        <span class="kh-bo__specrow-value">
                                            <a href="{{ route('resumes.show', $application->resume) }}">{{ __('ui.bo.applications.open_resume') }}</a>
                                        </span>
                                    @endif
                                </div>
                            </li>

                            <li class="kh-bo__specrow">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
                                </svg>
                                <div class="kh-bo__specrow-body">
                                    <span class="kh-bo__specrow-label">{{ __('ui.bo.applications.account') }}</span>
                                    <span class="kh-bo__specrow-value">
                                        @if ($application->candidate)
                                            {{ $application->candidate->displayName() }}
                                        @else
                                            The account has been removed
                                        @endif
                                    </span>
                                </div>
                            </li>

                            <li class="kh-bo__specrow">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                                </svg>
                                <div class="kh-bo__specrow-body">
                                    <span class="kh-bo__specrow-label">{{ __('ui.bo.applications.applied') }}</span>
                                    <span class="kh-bo__specrow-value">{{ $appliedAt?->format('d M Y, H:i') ?: '—' }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
