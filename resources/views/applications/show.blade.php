@extends('layouts.admin')

@section('title', ($application->jobPost?->title ?? __('ui.bo.applications.title')) . ' | ' . __('ui.bo.mine.title'))

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $post = $application->jobPost;
        $appliedAt = $application->applied_at ?? $application->created_at;
        $decidedAt = $application->decidedAt();
        $tones = \App\Models\JobApplication::statusTones();

        // Rejected is an end state rather than a step past shortlisted, so the
        // trail shows it only when the row landed there — and drops Hired when
        // it did, since a rejected application is never going to reach it.
        $rejected = $application->status === \App\Models\JobApplication::STATUS_REJECTED;
        $pipeline = collect(\App\Models\JobApplication::statuses())
            ->reject(fn (string $step) => $step === \App\Models\JobApplication::STATUS_REJECTED && ! $rejected)
            ->reject(fn (string $step) => $step === \App\Models\JobApplication::STATUS_HIRED && $rejected)
            ->values()
            ->all();
        $reached = array_search($application->status, $pipeline, true);
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('my-applications') }}">{{ __('ui.bo.mine.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $post?->title ?? 'Application' }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ $post?->title ?? 'Job post removed' }}</h1>
                <p>
                    @if ($post)
                        {{ $post->company }}
                        @if ($post->location)
                            · {{ $post->location }}
                        @endif
                        · applied {{ $application->appliedAgo() }}
                    @else
                        This job post has been taken down, but your application is still on record.
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('my-applications') }}">{{ __('ui.bo.mine.back_to_mine') }}</a>

                @if ($post?->isPublished())
                    <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('jobs.show', $post->slug) }}"
                        target="_blank" rel="noopener">{{ __('ui.bo.mine.view_posting') }}</a>
                @endif

                @if ($application->isCancellable())
                    <form method="POST" action="{{ route('my-applications.cancel', $application) }}"
                        onsubmit="return confirm('Withdraw your application for “{{ addslashes($post?->title ?? 'this job') }}”? You can apply again later.');">
                        @csrf
                        @method('DELETE')
                        <button class="kh-bo__btn kh-bo__btn--ghost kh-bo__btn--danger" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                            </svg>
                            {{ __('ui.bo.mine.withdraw_application') }}
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <div class="kh-bo__detail">
            <div class="kh-bo__detail-main">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.mine.where_it_stands') }}</h2>
                            <p>{{ __('ui.bo.mine.where_it_stands_hint') }}</p>
                        </div>

                        <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                            {{ __('ui.bo.status.' . $application->status) }}
                        </span>
                    </div>

                    <div class="kh-bo__card-body">
                        <ol class="kh-bo__steps">
                            @foreach ($pipeline as $index => $step)
                                <li @class([
                                    'is-done' => $reached !== false && $index < $reached,
                                    'is-current' => $step === $application->status,
                                ]) data-tone="{{ $tones[$step] ?? 'neutral' }}">
                                    <span aria-hidden="true"></span>
                                    {{ ucfirst($step) }}
                                </li>
                            @endforeach
                        </ol>

                        <dl class="kh-bo__facts">
                            <div>
                                <dt>{{ __('ui.bo.applications.applied') }}</dt>
                                <dd>{{ $appliedAt?->format('d M Y, H:i') ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt>{{ $application->decisionLabel() ?? 'Employer decision' }}</dt>
                                <dd>
                                    @if ($decidedAt)
                                        {{ $decidedAt->format('d M Y, H:i') }}
                                        <span class="kh-bo__ref">{{ $decidedAt->diffForHumans() }}</span>
                                    @elseif ($application->decisionLabel())
                                        {{-- Moved before this date was recorded. --}}
                                        <span class="kh-bo__ref">{{ __('ui.bo.mine.date_not_recorded') }}</span>
                                    @else
                                        <span class="kh-bo__ref">{{ __('ui.bo.mine.not_opened') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt>{{ __('ui.bo.mine.can_withdraw') }}</dt>
                                <dd>
                                    @if ($application->isCancellable())
                                        Yes — the employer has not opened it yet
                                    @else
                                        No — the employer has already reviewed it
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>

                @if ($application->candidate_message)
                    <section class="kh-bo__card">
                        <div class="kh-bo__card-head">
                            <div>
                                <h2>{{ __('ui.bo.mine.employer_message') }}</h2>
                                <p>
                                    Written by the hiring team at {{ $post?->company ?? 'the company' }}@if ($decidedAt), {{ $decidedAt->diffForHumans() }}@endif.
                                </p>
                            </div>
                        </div>

                        <div class="kh-bo__card-body">
                            <p class="kh-bo__prose">{{ $application->candidate_message }}</p>
                        </div>
                    </section>
                @endif

            </div>

            <aside class="kh-bo__detail-side">
                <section class="kh-bo__card">
                    <div class="kh-bo__card-head">
                        <div>
                            <h2>{{ __('ui.bo.mine.what_you_sent') }}</h2>
                            <p>{{ __('ui.bo.mine.what_you_sent_hint') }}</p>
                        </div>
                    </div>

                    <div class="kh-bo__card-body">
                        {{-- Name and email are the identity, so they head the card
                             rather than queueing up as two more rows. --}}
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
                                <a href="mailto:{{ $application->email }}">{{ $application->email }}</a>
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
                                    @if ($application->cvLabel())
                                        <span class="kh-bo__filechip">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M21.4 11.05L12.25 20.2a5.5 5.5 0 01-7.78-7.78l9.19-9.19a3.67 3.67 0 015.19 5.19l-9.2 9.19a1.83 1.83 0 01-2.59-2.59l8.49-8.48" />
                                            </svg>
                                            {{ $application->cvLabel() }}
                                        </span>
                                    @else
                                        <span class="kh-bo__specrow-value">{{ __('ui.bo.mine.no_cv_attached') }}</span>
                                    @endif
                                </div>
                            </li>

                            @if ($application->message)
                                <li class="kh-bo__specrow">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z" />
                                    </svg>
                                    <div class="kh-bo__specrow-body">
                                        <span class="kh-bo__specrow-label">{{ __('ui.bo.mine.your_message') }}</span>
                                        <p class="kh-bo__quote">{{ $application->message }}</p>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </section>

                @if ($post)
                    @php
                        // Icon per fact, keyed to the label toCatalogArray() ships.
                        $specIcons = [
                            'Job type' => '<path d="M20 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />',
                            'Work mode' => '<path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0118 0z" /><circle cx="12" cy="10" r="3" />',
                            'Experience' => '<path d="M23 6l-9.5 9.5-5-5L1 18" /><path d="M17 6h6v6" />',
                            'Department' => '<rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" />',
                        ];
                        $urgent = $post->deadlineTone() === 'urgent';
                    @endphp

                    <section class="kh-bo__card">
                        <div class="kh-bo__card-head">
                            <div>
                                <h2>{{ __('ui.bo.mine.the_job') }}</h2>
                                <p>{{ __('ui.bo.mine.the_job_hint') }}</p>
                            </div>

                            @unless ($post->isPublished())
                                <span class="kh-bo__status kh-bo__status--pending">{{ __('ui.bo.mine.closed') }}</span>
                            @endunless
                        </div>

                        <div class="kh-bo__card-body">
                            @if (filled($post->summary))
                                <p class="kh-bo__prose">{{ $post->summary }}</p>
                            @endif

                            {{-- Tiles rather than a fact list: six short values scan
                                 far better two-up than stacked down the column. --}}
                            <div class="kh-bo__specgrid">
                                @foreach ($post->toCatalogArray()['detail_items'] as $item)
                                    <div class="kh-bo__spectile">
                                        <span class="kh-bo__spectile-head">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">{!! $specIcons[$item['label']] ?? '<circle cx="12" cy="12" r="9" />' !!}</svg>
                                            {{ $item['label'] }}
                                        </span>
                                        <strong>{{ $item['value'] ?: '—' }}</strong>
                                    </div>
                                @endforeach

                                <div class="kh-bo__spectile kh-bo__spectile--wide">
                                    <span class="kh-bo__spectile-head">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <line x1="12" y1="1" x2="12" y2="23" /><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                                        </svg>
                                        {{ __('ui.bo.job_detail.salary') }}
                                    </span>
                                    <strong>{{ $post->salary ?: 'Undisclosed' }}</strong>
                                </div>

                                <div @class(['kh-bo__spectile', 'kh-bo__spectile--wide', 'kh-bo__spectile--urgent' => $urgent])>
                                    <span class="kh-bo__spectile-head">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                                        </svg>
                                        {{ __('ui.bo.job_detail.deadline') }}
                                    </span>
                                    <strong>{{ $post->deadlineLabel() }}</strong>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection
