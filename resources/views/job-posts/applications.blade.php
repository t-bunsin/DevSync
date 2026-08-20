@extends('layouts.admin')

@section('title', 'Applications · ' . $post->title . ' | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@push('scripts')
    <script src="{{ asset('js/backoffice-modal.js') }}?v={{ filemtime(public_path('js/backoffice-modal.js')) }}" defer></script>
@endpush

@section('main-content')
    @php
        $total = $applications->count();

        $filters = [
            '' => 'All',
            'new' => 'New',
            'reviewing' => 'Reviewing',
            'shortlisted' => 'Shortlisted',
            'hired' => 'Hired',
            'rejected' => 'Rejected',
        ];

        $isFiltered = $activeStatus || $searchTerm || $fromDate || $toDate;
        $received = $post->applications()->count();

        // Carried on every filter link and on the search form, so narrowing by
        // one control never silently drops the others.
        $query = array_filter([
            'status' => $activeStatus,
            'q' => $searchTerm,
            'from' => $fromDate,
            'to' => $toDate,
        ]);
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.index') }}">Job posts</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('job-posts.show', $post) }}">{{ $post->title }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Applications</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Applications</h1>
                <p>
                    {{ $post->title }} · {{ $post->company }}
                    @if ($post->employer?->hasVerifiedCompliance())
                        <x-verified-badge :show-label="false" :size="15" />
                    @endif
                    @if ($post->isPublished())
                        · live at <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">/jobs/{{ $post->slug }}</a>
                    @else
                        · not published
                    @endif
                </p>
            </div>

            <div class="kh-bo__head-actions">
                <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.index') }}">Back to job posts</a>
                <a class="kh-bo__btn" href="{{ route('job-posts.show', $post) }}">Open the post</a>
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

        <section class="kh-bo__tiles" aria-label="Application summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 00-3-3.87" />
                    </svg>
                </span>
                <div><strong>{{ number_format($received) }}</strong><span>Candidates applied</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div>
                    <strong>{{ number_format($counts->get('new', 0) + $counts->get('reviewing', 0)) }}</strong>
                    <span>Awaiting a decision</span>
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
                    <span>Shortlisted or hired</span>
                </div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('rejected', 0)) }}</strong><span>Rejected</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Candidates</h2>
                    <p>
                        {{ $total }} {{ \Illuminate\Support\Str::plural('application', $total) }} shown.
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
                    <div class="kh-bo__filters">
                        @foreach ($filters as $value => $label)
                            <a class="kh-bo__filter{{ (string) $activeStatus === (string) $value ? ' is-active' : '' }}"
                                href="{{ route('job-posts.applications', ['jobPost' => $post->id] + array_filter($query + ['status' => $value])) }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form class="kh-bo__search" method="GET" action="{{ route('job-posts.applications', $post) }}" role="search">
                        @if ($activeStatus)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search name or email" aria-label="Search applications">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="Applied from date" title="Applied from date">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="Applied end date" title="Applied end date">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('job-posts.applications', $post) }}">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table kh-bo__table--dense">
                    <thead>
                        <tr>
                            <th scope="col">Candidate</th>
                            <th scope="col">Contact</th>
                            <th scope="col">CV</th>
                            <th scope="col">Applied</th>
                            <th scope="col">Status</th>
                            <th scope="col">Note</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            {{-- Block form, not @php(...): Blade pairs a bare @php with
                                 the next @endphp anywhere in the file, so an inline one
                                 here would swallow the markup down to the dialogs. --}}
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
                                                @if ($application->resume)
                                                    <a class="kh-bo__name-link" href="{{ route('resumes.show', $application->resume) }}">{{ $application->full_name }}</a>
                                                @else
                                                    {{ $application->full_name }}
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">{{ $application->email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="kh-bo__nowrap">
                                    <a href="mailto:{{ $application->email }}?subject={{ rawurlencode('Your application for ' . $post->title) }}">Contact</a>
                                    <span class="kh-bo__ref">{{ $application->phone ?: 'No phone' }}</span>
                                </td>

                                <td class="kh-bo__nowrap">
                                    @if ($application->cv_path)
                                        <a href="{{ route('job-applications.cv', $application) }}">Download</a>
                                        <span class="kh-bo__ref">Uploaded</span>
                                    @elseif ($application->resume)
                                        <a href="{{ route('resumes.download', $application->resume) }}">Download</a>
                                        <span class="kh-bo__ref">From resume</span>
                                    @else
                                        <span class="kh-bo__ref">No CV on file</span>
                                    @endif
                                </td>

                                <td class="kh-bo__posted">
                                    {{ ucfirst($application->appliedAgo()) }}
                                    <span class="kh-bo__ref">{{ $appliedAt?->format('d M Y') ?: '—' }}</span>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                                        {{ ucfirst($application->status) }}
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
                                        {{-- Every per-row function lives in this dialog: the message,
                                             the contact details, the status and the internal note. --}}
                                        <button class="kh-bo__action" type="button"
                                            data-bo-modal-open="application-{{ $application->id }}"
                                            title="Show details" aria-label="Show details for {{ $application->full_name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>

                                        <form method="POST" action="{{ route('job-applications.destroy', $application) }}"
                                            onsubmit="return confirm('Delete the application from {{ addslashes($application->full_name) }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="Delete application" aria-label="Delete the application from {{ $application->full_name }}">
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
                                        <strong>No applications</strong>
                                        <span>
                                            @if ($isFiltered)
                                                Nothing matches this filter.
                                                <a href="{{ route('job-posts.applications', $post) }}">Clear it</a> to see everything.
                                            @elseif (! $post->isPublished())
                                                This post is not published, so candidates cannot apply to it yet.
                                            @else
                                                Nobody has applied for this role yet. Applications sent from
                                                <a href="{{ route('jobs.show', $post->slug) }}" target="_blank" rel="noopener">the public job page</a>
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

        </section>

        {{-- One dialog per row: the candidate on the left, the employer's own
             review on the right. Rendered outside the table so the markup stays
             valid, and keyed by id to the button that opens it. --}}
        @foreach ($applications as $application)
            @php
                $appliedAt = $application->applied_at ?? $application->created_at;
                $reviewId = "application-{$application->id}-review";
                $tones = \App\Models\JobApplication::statusTones();
            @endphp

            <dialog class="kh-bo__modal" id="application-{{ $application->id }}"
                aria-labelledby="application-{{ $application->id }}-title">
                <header class="kh-bo__modal-head">
                    <span class="kh-bo__modal-avatar" aria-hidden="true">
                        @if ($application->photoUrl())
                            <img src="{{ $application->photoUrl() }}" alt="">
                        @else
                            {{ $application->initials() }}
                        @endif
                    </span>

                    <div class="kh-bo__modal-heading">
                        <h2 id="application-{{ $application->id }}-title">{{ $application->full_name }}</h2>
                        <p>
                            {{ $post->title }} · applied {{ $application->appliedAgo() }}
                            @if ($appliedAt)
                                <span class="kh-bo__ref">{{ $appliedAt->format('d M Y, H:i') }}</span>
                            @endif
                        </p>
                    </div>

                    <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                        {{ ucfirst($application->status) }}
                    </span>

                    <button class="kh-bo__modal-close" type="button" data-bo-modal-close
                        title="Close" aria-label="Close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                </header>

                <div class="kh-bo__modal-body">
                    <section class="kh-bo__modal-panel">
                        <h3 class="kh-bo__modal-legend">Candidate</h3>

                        <ul class="kh-bo__contact">
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="2" y="4" width="20" height="16" rx="2" /><path d="M2 7l10 6 10-6" />
                                </svg>
                                <a href="mailto:{{ $application->email }}?subject={{ rawurlencode('Your application for ' . $post->title) }}">{{ $application->email }}</a>
                            </li>

                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.2a2 2 0 012.1-.5c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z" />
                                </svg>
                                @if ($application->phone)
                                    <a href="tel:{{ $application->phone }}">{{ $application->phone }}</a>
                                @else
                                    <span class="kh-bo__ref">No phone given</span>
                                @endif
                            </li>

                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                                </svg>
                                @if ($application->cv_path)
                                    <span>
                                        <a href="{{ route('job-applications.cv', $application) }}">Download CV</a>
                                        <span class="kh-bo__ref">
                                            {{ $application->cvLabel() }}
                                            {{-- A candidate may upload a file *and* keep a resume
                                                 here; the employer should be able to read both. --}}
                                            @if ($application->resume)
                                                · also has <a href="{{ route('resumes.show', $application->resume) }}">a resume</a>
                                            @endif
                                        </span>
                                    </span>
                                @elseif ($application->resume)
                                    <span>
                                        <a href="{{ route('resumes.download', $application->resume) }}">Download CV</a>
                                        <span class="kh-bo__ref">
                                            From <a href="{{ route('resumes.show', $application->resume) }}">their resume</a>
                                        </span>
                                    </span>
                                @else
                                    <span class="kh-bo__ref">No CV on file</span>
                                @endif
                            </li>

                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" /><circle cx="12" cy="7" r="4" />
                                </svg>
                                @if ($application->candidate)
                                    <span>
                                        {{ $application->candidate->displayName() }}
                                        <span class="kh-bo__ref">{{ $application->candidate->email }}</span>
                                    </span>
                                @else
                                    <span class="kh-bo__ref">The account has been removed</span>
                                @endif
                            </li>
                        </ul>

                        @if ($application->message)
                            <blockquote class="kh-bo__quote">{{ $application->message }}</blockquote>
                        @else
                            <p class="kh-bo__ref kh-bo__quote-empty">No message was sent with this application.</p>
                        @endif
                    </section>

                    {{-- Status and the private note save together: both are the
                         employer's own record of the review, not the candidate's. --}}
                    <section class="kh-bo__modal-panel kh-bo__modal-panel--review">
                        <h3 class="kh-bo__modal-legend">Review</h3>

                        <form id="{{ $reviewId }}" method="POST"
                            action="{{ route('job-applications.update', $application) }}">
                            @csrf
                            @method('PATCH')

                            {{-- Radios, not a select: the five states are the whole
                                 pipeline, and seeing where this candidate sits in it
                                 is the point of opening the dialog. --}}
                            <fieldset class="kh-bo__pills">
                                <legend class="kh-bo__label">Status</legend>

                                @foreach (\App\Models\JobApplication::statuses() as $status)
                                    <label class="kh-bo__pill kh-bo__pill--{{ $tones[$status] ?? 'neutral' }}">
                                        <input type="radio" name="status" value="{{ $status }}"
                                            @checked($application->status === $status)>
                                        <span>{{ ucfirst($status) }}</span>
                                    </label>
                                @endforeach
                            </fieldset>

                            <label class="kh-bo__field">
                                <span class="kh-bo__label">Internal note</span>
                                <textarea class="kh-bo__control" name="note" rows="5"
                                    placeholder="Only the hiring team sees this.">{{ $application->note }}</textarea>
                            </label>
                        </form>
                    </section>
                </div>

                <footer class="kh-bo__modal-foot">
                    {{-- Its own form: a delete cannot nest inside the review form. --}}
                    <form method="POST" action="{{ route('job-applications.destroy', $application) }}"
                        onsubmit="return confirm('Delete the application from {{ addslashes($application->full_name) }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button class="kh-bo__btn kh-bo__btn--ghost kh-bo__btn--danger" type="submit">Delete</button>
                    </form>

                    <div class="kh-bo__modal-foot-actions">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="button" data-bo-modal-close>Close</button>
                        {{-- form=: the button lives outside the form it submits. --}}
                        <button class="kh-bo__btn" type="submit" form="{{ $reviewId }}">Save review</button>
                    </div>
                </footer>
            </dialog>
        @endforeach

    </div>
@endsection
