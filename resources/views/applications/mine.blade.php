@extends('layouts.admin')

@section('title', 'My Applications | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $applications->total();
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">My Applications</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>My Applications</h1>
                <p>The jobs you've applied to, and where each one stands.</p>
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

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Applications</h2>
                    <p>
                        {{ $total }} {{ \Illuminate\Support\Str::plural('application', $total) }}
                        {{ $isFiltered ? 'match this search.' : 'shown.' }}
                    </p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('my-applications') }}" role="search">
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search job or company" aria-label="Search your applications">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="Applied from date" title="Applied from date">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="Applied to date" title="Applied to date">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('my-applications') }}">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table kh-bo__table--dense">
                    <thead>
                        <tr>
                            <th scope="col">Job</th>
                            <th scope="col">Applied</th>
                            <th scope="col">Status</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $application)
                            @php
                                $post = $application->jobPost;
                                $appliedAt = $application->applied_at ?? $application->created_at;
                            @endphp
                            <tr>
                                <td>
                                    <div>
                                        <span class="kh-bo__name">
                                            {{-- Straight to this application's own page, not the public
                                                 posting: what the candidate wants here is their submission
                                                 and where it stands. --}}
                                            <a class="kh-bo__name-link" href="{{ route('my-applications.show', $application) }}">
                                                {{ $post?->title ?? 'Job post removed' }}
                                            </a>
                                        </span>
                                        @if ($post)
                                            <span class="kh-bo__ref">{{ $post->company }}</span>
                                        @endif
                                        @if ($application->candidate_message)
                                            <span class="kh-bo__ref">Message from the employer</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="kh-bo__posted">
                                    {{ ucfirst($application->appliedAgo()) }}
                                    <span class="kh-bo__ref">{{ $appliedAt?->format('d M Y') ?: '—' }}</span>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $application->statusTone() }}">
                                        {{ ucfirst($application->status) }}
                                    </span>

                                    @if ($decidedAt = $application->decidedAt())
                                        <span class="kh-bo__ref">
                                            {{ $application->decisionLabel() }} {{ $decidedAt->format('d M Y') }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        <a class="kh-bo__action" href="{{ route('my-applications.show', $application) }}"
                                            title="View application" aria-label="View your application for {{ $post?->title ?? 'this job' }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" /><circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </a>

                                        @if ($application->isCancellable())
                                            <form method="POST" action="{{ route('my-applications.cancel', $application) }}"
                                                onsubmit="return confirm('Withdraw your application for “{{ addslashes($post?->title ?? 'this job') }}”? You can apply again later.');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                    title="Withdraw application" aria-label="Withdraw your application for {{ $post?->title ?? 'this job' }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="kh-bo__empty">
                                        @if ($isFiltered)
                                            <strong>Nothing matches that search</strong>
                                            <span>
                                                <a href="{{ route('my-applications') }}">Clear it</a> to see every application.
                                            </span>
                                        @else
                                            <strong>No applications yet</strong>
                                            <span>
                                                Browse <a href="{{ route('jobs.index') }}">open roles</a> and apply — they'll show up here.
                                            </span>
                                        @endif
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
