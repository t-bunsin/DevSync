@extends('layouts.admin')

@section('title', 'Resumes | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $resumes->count();

        $filters = [
            '' => 'All',
            'published' => 'Published',
            'draft' => 'Draft',
            'archived' => 'Archived',
        ];

        $isFiltered = $activeStatus || $searchTerm || $fromDate || $toDate;

        // Reuses the three badge tones the back office already ships, the same
        // way the job post list maps draft/published/closed onto them.
        $tones = ['published' => 'verified', 'draft' => 'pending', 'archived' => 'rejected'];
    @endphp

    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Resumes</h1>
                <p>Register and maintain the candidate CVs held on the platform.</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('resumes.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Add resume
            </a>
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

        <section class="kh-bo__tiles" aria-label="Resume summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>Resumes</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('published', 0)) }}</strong><span>Published</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('draft', 0)) }}</strong><span>Drafts</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="10" rx="2" /><path d="M7 11V7a5 5 0 0110 0v4" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('archived', 0)) }}</strong><span>Archived</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Resume register</h2>
                    <p>
                        {{ $total }} {{ \Illuminate\Support\Str::plural('resume', $total) }} shown.
                        @if ($fromDate || $toDate)
                            Registered
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
                                href="{{ route('resumes.index', array_filter(['status' => $value, 'q' => $searchTerm, 'from' => $fromDate, 'to' => $toDate])) }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form class="kh-bo__search" method="GET" action="{{ route('resumes.index') }}" role="search">
                        @if ($activeStatus)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search name or headline" aria-label="Search resumes">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="Registered from date" title="Registered from date">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="Registered end date" title="Registered end date">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('resumes.index') }}">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">Candidate</th>
                            <th scope="col">Headline</th>
                            <th scope="col">Status</th>
                            <th scope="col">Sections</th>
                            <th scope="col">Registered</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
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
                                        <a class="kh-bo__action" href="{{ route('resumes.download', $resume) }}"
                                            title="Download PDF" aria-label="Download {{ $resume->full_name }} as PDF">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 3v12" /><path d="M7 12l5 5 5-5" /><path d="M4 21h16" />
                                            </svg>
                                        </a>

                                        <a class="kh-bo__action" href="{{ route('resumes.show', $resume) }}"
                                            title="Preview resume" aria-label="Preview {{ $resume->full_name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>

                                        <a class="kh-bo__action" href="{{ route('resumes.edit', $resume) }}"
                                            title="Edit resume" aria-label="Edit {{ $resume->full_name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('resumes.destroy', $resume) }}"
                                            onsubmit="return confirm('Delete the resume for {{ addslashes($resume->full_name) }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="Delete resume" aria-label="Delete {{ $resume->full_name }}">
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
                                <td colspan="6">
                                    <div class="kh-bo__empty">
                                        <strong>No resumes yet</strong>
                                        <span>
                                            @if ($isFiltered)
                                                No resumes match this filter.
                                                <a href="{{ route('resumes.index') }}">Clear it</a> to see everything.
                                            @else
                                                Register the first resume to get started.
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
