@extends('layouts.admin')

@section('title', 'Applications · ' . $post->title . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $applications->count();

        $filters = [
            '' => __('ui.bo.all'),
            'new' => __('ui.bo.status.new'),
            'reviewing' => __('ui.bo.status.reviewing'),
            'shortlisted' => __('ui.bo.status.shortlisted'),
            'hired' => __('ui.bo.status.hired'),
            'rejected' => __('ui.bo.status.rejected'),
        ];

        $isFiltered = $activeStatus || $searchTerm || $fromDate || $toDate;
        $received = $post->applications()->count();
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.index') }}">{{ __('ui.bo.job_posts.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.show', $post) }}">{{ $post->title }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.applications.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.applications.title') }}</h1>
                <p>
                    {{ $post->title }} · {{ $post->company }}
                    @if ($post->employer?->hasVerifiedCompliance())
                        <x-verified-badge :show-label="false" :size="15" />
                    @endif
                    @if ($post->isPublished())
                        · {{ __('ui.bo.job_detail.live_at') }} <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                    @else
                        · not published
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">{{ __('ui.bo.applications.back_to_posts') }}</a>
                <a class="kh-bo__btn" href="{{ route('job-posts.show', $post) }}">{{ __('ui.bo.applications.open_post') }}</a>
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

        <section class="kh-bo__tiles" aria-label="{{ __('ui.bo.applications.summary_label') }}">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 00-3-3.87" />
                    </svg>
                </span>
                <div><strong>{{ number_format($received) }}</strong><span>{{ __('ui.bo.applications.tile_applied') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div>
                    <strong>{{ number_format($counts->get('new', 0) + $counts->get('reviewing', 0)) }}</strong>
                    <span>{{ __('ui.bo.applications.tile_awaiting') }}</span>
                </div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3l2.6 5.6 6 .9-4.3 4.3 1 6.2-5.3-3-5.3 3 1-6.2L3.4 9.5l6-.9z" />
                    </svg>
                </span>
                <div>
                    <strong>{{ number_format($counts->get('shortlisted', 0) + $counts->get('hired', 0)) }}</strong>
                    <span>{{ __('ui.bo.applications.tile_shortlisted') }}</span>
                </div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('rejected', 0)) }}</strong><span>{{ __('ui.bo.applications.tile_rejected') }}</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ __('ui.bo.applications.candidates') }}</h2>
                    <p>
                        {{ trans_choice('ui.bo.billing.applications_shown', $total, ['count' => $total]) }}
                        @if ($fromDate || $toDate)
                            Applied
                            @if ($fromDate && $toDate)
                                {{ $fromDate }} – {{ $toDate }}.
                            @elseif ($fromDate)
                                from {{ $fromDate }}.
                            @else
                                up to {{ $toDate }}.
                            @endif
                        @endif
                    </p>
                </div>

                <div class="kh-bo__tools">
                    {{-- Status is a field in the search form, not a row of links:
                         one Search applies the status, the term and the dates
                         together, instead of the status jumping on click. --}}
                    <form class="kh-bo__search" method="GET" action="{{ route('job-posts.applications', $post) }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => __('ui.bo.applications.filter_status'),
                            'allLabel' => __('ui.bo.applications.all_statuses'),
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.applications.search_placeholder') }}" aria-label="{{ __('ui.bo.applications.search_aria') }}">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="{{ __('ui.bo.applications.from_date') }}" title="{{ __('ui.bo.applications.from_date') }}">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="{{ __('ui.bo.applications.to_date') }}" title="{{ __('ui.bo.applications.to_date') }}">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.applications', $post) }}">{{ __('ui.bo.clear') }}</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table kh-bo__table--dense">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.applications.col_candidate') }}</th>
                            <th scope="col">{{ __('ui.bo.applications.col_contact') }}</th>
                            <th scope="col">CV</th>
                            <th scope="col">{{ __('ui.bo.applications.col_applied') }}</th>
                            <th scope="col">{{ __('ui.bo.applications.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.applications.col_note') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            {{-- Block form, not @php(...): Blade pairs a bare @php with
                                 the next @endphp anywhere in the file, so an inline one
                                 here would swallow every row below it. --}}
                            @php
                                $appliedAt = $application->applied_at ?? $application->created_at;
                            @endphp
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo kh-bo__logo--round" aria-hidden="true">
                                            @if ($application->photoUrl())
                                                <img src="{{ $application->photoUrl() }}" alt="">
                                            @else
                                                {{ $application->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('job-applications.show', $application) }}">{{ $application->full_name }}</a>
                                            </span>
                                            <span class="kh-bo__ref">{{ $application->email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="kh-bo__nowrap">
                                    <a href="mailto:{{ $application->email }}?subject={{ rawurlencode(__('ui.bo.applications.contact_subject', ['title' => $post->title])) }}">{{ __('ui.bo.applications.contact') }}</a>
                                    <span class="kh-bo__ref">{{ $application->phone ?: 'No phone' }}</span>
                                </td>

                                <td class="kh-bo__nowrap">
                                    {{-- An uploaded CV is the applicant's file (application.download);
                                         the fallback is a resume record, so it follows resume.download. --}}
                                    @if ($application->cv_path)
                                        @if (auth()->user()?->hasPermission(\App\Models\Permission::APPLICATION_DOWNLOAD))
                                            <a href="{{ route('job-applications.cv', $application) }}">{{ __('ui.bo.applications.download') }}</a>
                                        @endif
                                        <span class="kh-bo__ref">{{ __('ui.bo.applications.uploaded') }}</span>
                                    @elseif ($application->resume)
                                        @if (auth()->user()?->hasPermission(\App\Models\Permission::RESUME_DOWNLOAD))
                                            <a href="{{ route('resumes.download', $application->resume) }}">{{ __('ui.bo.applications.download') }}</a>
                                        @endif
                                        <span class="kh-bo__ref">{{ __('ui.bo.applications.from_resume') }}</span>
                                    @else
                                        <span class="kh-bo__ref">{{ __('ui.bo.applications.no_cv') }}</span>
                                    @endif
                                </td>

                                <td class="kh-bo__posted">
                                    {{ ucfirst($application->appliedAgo()) }}
                                    <span class="kh-bo__ref">{{ $appliedAt?->format('d M Y') ?: '—' }}</span>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                                        {{ __('ui.bo.status.' . $application->status) }}
                                    </span>
                                </td>

                                <td class="kh-bo__truncate">
                                    @if ($application->note)
                                        {{ $application->note }}
                                    @else
                                        <span class="kh-bo__ref">—</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        {{-- Every per-row function lives on the candidate page now:
                                             the message, the contact details, the status, the internal
                                             note and the message back to the candidate. --}}
                                        <a class="kh-bo__action" href="{{ route('job-applications.show', $application) }}"
                                            title="{{ __('ui.bo.applications.open_candidate') }}" aria-label="{{ __('ui.bo.applications.open_aria', ['name' => $application->full_name]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('job-applications.destroy', $application) }}"
                                            onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $application->full_name])) }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="{{ __('ui.bo.applications.delete_application') }}" aria-label="{{ __('ui.bo.applications.delete_aria', ['name' => $application->full_name]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.applications.empty_title') }}</strong>
                                        <span>
                                            @if ($isFiltered)
                                                Nothing matches this filter.
                                                <a href="{{ route('job-posts.applications', $post) }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @elseif (! $post->isPublished())
                                                This post is not published, so candidates cannot apply to it yet.
                                            @else
                                                Nobody has applied for this role yet. Applications sent from
                                                <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">{{ __('ui.bo.applications.public_job_page') }}</a>
                                                land here.
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.kh-bo-pagination', ['paginator' => $applications])
        </section>

    </div>
@endsection
