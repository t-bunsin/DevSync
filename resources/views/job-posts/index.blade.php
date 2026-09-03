@extends('layouts.admin')

@section('title', __('ui.bo.job_posts.title') . ' | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $posts->count();
        $filters = [
            '' => __('ui.bo.job_posts.status_all'),
            'published' => __('ui.bo.job_posts.status_published'),
            'draft' => __('ui.bo.job_posts.status_draft'),
            'closed' => __('ui.bo.job_posts.status_closed'),
        ];
        $isFiltered = $activeStatus || $searchTerm || $fromDate || $toDate;
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.job_posts.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.job_posts.title') }}</h1>
                <p>{{ __('ui.bo.job_posts.subtitle') }}</p>
            </div>

            <div class="kh-bo__head-actions">
                @if (auth()->user()?->hasPermission(\App\Models\Permission::JOB_DOWNLOAD))
                    <a class="kh-bo__btn kh-bo__btn--ghost"
                        href="{{ route('job-posts.export', array_filter(['status' => $activeStatus, 'q' => $searchTerm, 'from' => $fromDate, 'to' => $toDate])) }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3v12" /><path d="M7 10l5 5 5-5" /><path d="M4 21h16" />
                        </svg>
                        {{ __('ui.bo.job_posts.export') }}
                    </a>
                @endif

                @if (auth()->user()?->hasPermission(\App\Models\Permission::JOB_CREATE))
                    <a class="kh-bo__btn" href="{{ route('job-posts.create') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        {{ __('ui.bo.job_posts.add') }}
                    </a>
                @endif
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

        @if ($counts->get('published', 0) === 0)
            <div class="kh-bo__errors" role="status">
                <strong>{{ __('ui.bo.job_posts.no_published_title') }}</strong>
                {{ __('ui.bo.job_posts.no_published_body') }}
            </div>
        @endif

        <section class="kh-bo__tiles" aria-label="{{ __('ui.bo.job_posts.summary_label') }}">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" /><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>{{ __('ui.bo.job_posts.tile_posts') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('published', 0)) }}</strong><span>{{ __('ui.bo.job_posts.tile_published') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('draft', 0)) }}</strong><span>{{ __('ui.bo.job_posts.tile_drafts') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('closed', 0)) }}</strong><span>{{ __('ui.bo.job_posts.tile_closed') }}</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ __('ui.bo.job_posts.all_posts') }}</h2>
                    <p>
                        {{ trans_choice('ui.bo.job_posts.posts_shown', $total, ['count' => $total]) }}
                        @if ($fromDate && $toDate)
                            {{ __('ui.bo.job_posts.posted_between', ['from' => $fromDate, 'to' => $toDate]) }}
                        @elseif ($fromDate)
                            {{ __('ui.bo.job_posts.posted_from', ['from' => $fromDate]) }}
                        @elseif ($toDate)
                            {{ __('ui.bo.job_posts.posted_to', ['to' => $toDate]) }}
                        @endif
                    </p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('job-posts.index') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => __('ui.bo.job_posts.filter_status'),
                            'allLabel' => __('ui.bo.job_posts.all_statuses'),
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.job_posts.search_placeholder') }}" aria-label="{{ __('ui.bo.job_posts.search_aria') }}">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="{{ __('ui.bo.job_posts.from_date') }}" title="{{ __('ui.bo.job_posts.from_date') }}">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="{{ __('ui.bo.job_posts.to_date') }}" title="{{ __('ui.bo.job_posts.to_date') }}">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">{{ __('ui.bo.clear') }}</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table kh-bo__table--dense">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.job_posts.col_role') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_location') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_type') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_registered') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_applications') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_posted') }}</th>
                            <th scope="col">{{ __('ui.bo.job_posts.col_post_by') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($post->employer?->logoUrl())
                                                <img src="{{ $post->employer->logoUrl() }}" alt="">
                                            @elseif ($post->employer)
                                                {{ $post->employer->initials() }}
                                            @else
                                                {{ strtoupper(substr($post->company, 0, 2)) }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                <a class="kh-bo__name-link" href="{{ route('job-posts.show', $post) }}">{{ $post->title }}</a>
                                                @if ($post->featured)
                                                    <span class="kh-bo__flag kh-bo__flag--featured" title="{{ __('ui.bo.job_posts.featured') }}">
                                                        <i class="fas fa-star" aria-hidden="true"></i>
                                                        <span class="visually-hidden">{{ __('ui.bo.job_posts.featured') }}</span>
                                                    </span>
                                                @endif
                                                @if ($post->highlighted)
                                                    <span class="kh-bo__flag kh-bo__flag--spotlight" title="{{ __('ui.bo.job_posts.spotlight') }}">
                                                        <i class="fas fa-bolt" aria-hidden="true"></i>
                                                        <span class="visually-hidden">{{ __('ui.bo.job_posts.spotlight') }}</span>
                                                    </span>
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">
                                                {{ $post->company }}
                                                @if ($post->employer?->hasVerifiedCompliance())
                                                    <x-verified-badge :show-label="false" :size="14" />
                                                @endif
                                                · /jobs/{{ $post->slug }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="kh-bo__cell-location">{{ $post->location }}</td>
                                <td class="kh-bo__cell-type">{{ $post->type }} · {{ $post->mode }}</td>

                                <td class="kh-bo__nowrap">
                                    @if ($post->created_at)
                                        {{ $post->created_at->format('d M Y') }}
                                    @else
                                        <span class="kh-bo__ref">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $post->status === 'published' ? 'verified' : ($post->status === 'draft' ? 'pending' : 'rejected') }}">
                                        {{ __('ui.bo.job_posts.status_' . $post->status) }}
                                    </span>
                                </td>

                                {{-- Candidates who actually applied. The number links
                                     through to the rows it is counting. --}}
                                <td class="kh-bo__nowrap">
                                    @php($applicants = $post->applicantCount())
                                    @if (auth()->user()?->hasPermission(\App\Models\Permission::APPLICATION_VIEW))
                                    <a class="kh-bo__count{{ $applicants === 0 ? ' kh-bo__count--empty' : '' }}"
                                        href="{{ route('job-posts.applications', $post) }}"
                                        title="{{ __('ui.bo.job_posts.applicants_title', ['title' => $post->title]) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 00-3-3.87" /><path d="M16 3.13a4 4 0 010 7.75" />
                                        </svg>
                                        {{ number_format($applicants) }}
                                        <span class="visually-hidden">
                                            {{ trans_choice('ui.bo.job_posts.applications_for', $applicants, ['title' => $post->title]) }}
                                        </span>
                                    </a>
                                    @else
                                        {{-- Without application.view the count still reads, but
                                             there is nowhere for it to lead. --}}
                                        <span class="kh-bo__count kh-bo__count--empty">{{ number_format($applicants) }}</span>
                                    @endif
                                    <span class="kh-bo__ref">
                                        @if ($applicants === 0)
                                            {{ __('ui.bo.job_posts.no_candidates') }}
                                        @else
                                            {{ trans_choice('ui.bo.job_posts.candidates_applied', $applicants) }}
                                        @endif
                                    </span>
                                </td>

                                <td class="kh-bo__posted">
                                    @if ($post->isPublished())
                                        {{ ucfirst($post->postedAgo()) }}
                                        <span class="kh-bo__ref kh-bo__deadline kh-bo__deadline--{{ $post->deadlineTone() }}">
                                            {{ $post->deadlineLabel() }}
                                        </span>
                                    @else
                                        <span class="kh-bo__ref">{{ __('ui.bo.job_posts.not_live') }}</span>
                                    @endif
                                </td>

                                <td class="kh-bo__truncate">
                                    @if ($post->author)
                                        {{ $post->author->displayName() }}
                                        <span class="kh-bo__ref">{{ $post->author->email }}</span>
                                    @else
                                        <span class="kh-bo__ref">{{ __('ui.bo.unknown') }}</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        @if ($post->isPublished())
                                            <a class="kh-bo__action" href="{{ route('jobs.show', $post->slug) }}"
                                                target="_blank" rel="noopener"
                                                title="{{ __('ui.bo.job_posts.view_on_site') }}" aria-label="{{ __('ui.bo.job_posts.view_on_site_aria', ['title' => $post->title]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" /><path d="M15 3h6v6" /><path d="M10 14L21 3" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if (auth()->user()?->hasPermission(\App\Models\Permission::JOB_EDIT))
                                            <a class="kh-bo__action" href="{{ route('job-posts.edit', $post) }}"
                                                title="{{ __('ui.bo.job_posts.edit') }}" aria-label="{{ __('ui.bo.job_posts.edit_aria', ['title' => $post->title]) }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if (auth()->user()?->hasPermission(\App\Models\Permission::JOB_DELETE))
                                            <form method="POST" action="{{ route('job-posts.destroy', $post) }}"
                                                onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $post->title])) }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                    title="{{ __('ui.bo.job_posts.delete') }}" aria-label="{{ __('ui.bo.job_posts.delete_aria', ['title' => $post->title]) }}">
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
                                <td colspan="9">
                                    <div class="kh-bo__empty">
                                        <strong>{{ __('ui.bo.job_posts.empty_title') }}</strong>
                                        <span>
                                            @if ($isFiltered)
                                                {{ __('ui.bo.job_posts.empty_filtered') }}
                                                <a href="{{ route('job-posts.index') }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.job_posts.empty_none') }}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('partials.kh-bo-pagination', ['paginator' => $posts])
        </section>
    </div>
@endsection
