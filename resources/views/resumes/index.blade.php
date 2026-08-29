@extends('layouts.admin')

@section('title', 'Resumes | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $resumes->count();

        $filters = [
            '' => __('ui.bo.all'),
            'published' => __('ui.bo.status.published'),
            'draft' => __('ui.bo.status.draft'),
            'archived' => __('ui.bo.status.archived'),
        ];

        $isFiltered = $activeStatus || $searchTerm || $fromDate || $toDate;

        // Reuses the three badge tones the back office already ships, the same
        // way the job post list maps draft/published/closed onto them.
        $tones = ['published' => 'verified', 'draft' => 'pending', 'archived' => 'rejected'];
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ $isCandidate ? 'My Resume' : 'Resumes' }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ $isCandidate ? 'My Resume' : 'Resumes' }}</h1>
                <p>
                    {{ $isCandidate
                        ? 'The CV employers see when you apply. Only you can edit it.'
                        : 'Register and maintain the candidate CVs held on the platform.' }}
                </p>
            </div>

            @if ($can['create'])
                <a class="kh-bo__btn" href="{{ route('resumes.create') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    {{ $isCandidate ? 'Create my resume' : 'Add resume' }}
                </a>
            @endif
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

        <section class="kh-bo__tiles" aria-label="{{ __('ui.bo.resumes.summary_label') }}">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>{{ __('ui.bo.resumes.tile_resumes') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('published', 0)) }}</strong><span>{{ __('ui.bo.resumes.tile_published') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('draft', 0)) }}</strong><span>{{ __('ui.bo.resumes.tile_drafts') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('archived', 0)) }}</strong><span>{{ __('ui.bo.resumes.tile_archived') }}</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ __('ui.bo.resumes.register') }}</h2>
                    <p>
                        {{ trans_choice('ui.bo.resumes.resumes_shown', $total, ['count' => $total]) }}
                        @if ($fromDate && $toDate)
                            {{ __('ui.bo.resumes.registered_between', ['from' => $fromDate, 'to' => $toDate]) }}
                        @elseif ($fromDate)
                            {{ __('ui.bo.resumes.registered_from', ['from' => $fromDate]) }}
                        @elseif ($toDate)
                            {{ __('ui.bo.resumes.registered_to', ['to' => $toDate]) }}
                        @endif
                    </p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('resumes.index') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => __('ui.bo.resumes.filter_status'),
                            'allLabel' => __('ui.bo.resumes.all_statuses'),
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.resumes.search_placeholder') }}" aria-label="{{ __('ui.bo.resumes.search_aria') }}">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="{{ __('ui.bo.resumes.from_date') }}" title="{{ __('ui.bo.resumes.from_date') }}">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="{{ __('ui.bo.resumes.to_date') }}" title="{{ __('ui.bo.resumes.to_date') }}">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">{{ __('ui.bo.clear') }}</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.resumes.col_candidate') }}</th>
                            <th scope="col">{{ __('ui.bo.resumes.col_headline') }}</th>
                            <th scope="col">{{ __('ui.bo.resumes.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.resumes.col_sections') }}</th>
                            <th scope="col">{{ __('ui.bo.resumes.col_registered') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resumes as $resume)
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($resume->photoUrl())
                                                <img src="{{ $resume->photoUrl() }}" alt="">
                                            @else
                                                {{ $resume->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('resumes.show', $resume) }}">{{ $resume->full_name }}</a>
                                            </span>
                                            <span class="kh-bo__ref">{{ $resume->email ?: 'No email' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $resume->headline ?: '—' }}
                                    @if ($resume->location)
                                        <span class="kh-bo__ref">{{ $resume->location }}</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $tones[$resume->status] ?? 'pending' }}">
                                        {{ ucfirst($resume->status) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $resume->filledSectionCount() }} of 5 filled
                                    <span class="kh-bo__ref">
                                        {{ count($resume->section('work_history')) }}
                                        {{ \Illuminate\Support\Str::plural('role', count($resume->section('work_history'))) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $resume->created_at?->format('M j, Y') }}
                                    <span class="kh-bo__ref">
                                        {{ $resume->author?->displayName() ?? 'Unknown' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        @if ($can['download'])
                                            <a class="kh-bo__action" href="{{ route('resumes.download', $resume) }}"
                                                title="{{ __('ui.bo.resumes.download_pdf') }}" aria-label="{{ __('ui.bo.resumes.download_aria', ['name' => $resume->full_name]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 3v12" /><path d="M7 12l5 5 5-5" /><path d="M4 21h16" />
                                                </svg>
                                            </a>
                                        @endif

                                        <a class="kh-bo__action" href="{{ route('resumes.show', $resume) }}"
                                            title="{{ __('ui.bo.resumes.preview_resume') }}" aria-label="{{ __('ui.bo.resumes.preview_aria', ['name' => $resume->full_name]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>

                                        @if ($can['edit'])
                                            <a class="kh-bo__action" href="{{ route('resumes.edit', $resume) }}"
                                                title="{{ __('ui.bo.resumes.edit_resume') }}" aria-label="{{ __('ui.bo.resumes.edit_aria', ['name' => $resume->full_name]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if ($can['delete'])
                                            <form method="POST" action="{{ route('resumes.destroy', $resume) }}"
                                                onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $resume->full_name])) }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                    title="{{ __('ui.bo.resumes.delete_resume') }}" aria-label="{{ __('ui.bo.resumes.delete_aria', ['name' => $resume->full_name]) }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.resumes.empty_title') }}</strong>
                                        <span>
                                            @if ($isFiltered)
                                                {{ __('ui.bo.resumes.empty_filtered') }}
                                                <a href="{{ route('resumes.index') }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.resumes.empty_none') }}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
