@extends('layouts.admin')

@section('title', 'Companies | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $companies->count();
        $filters = ['' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected'];
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Companies</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Companies</h1>
                <p>Employers on the platform, the licences they hold, and the roles they advertise.</p>
            </div>

            @if (auth()->user()?->isAdmin())
                <a class="kh-bo__btn" href="{{ route('companies.create') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Add company
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

        @if ($errors->any())
            <div class="kh-bo__errors" role="alert">
                @foreach ($errors->all() as $message)
                    <div>{{ $message }}</div>
                @endforeach
            </div>
        @endif

        <section class="kh-bo__tiles" aria-label="Company summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18" /><path d="M5 21V7l7-4 7 4v14" /><path d="M9 9h2M9 13h2M9 17h2M13 9h2M13 13h2M13 17h2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>Companies</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('approved', 0)) }}</strong><span>Approved</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->get('pending', 0)) }}</strong><span>Awaiting approval</span></div>
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
                    <h2>Company directory</h2>
                    <p>{{ $total }} {{ \Illuminate\Support\Str::plural('company', $total) }} shown.</p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('companies') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => 'Filter by status',
                            'allLabel' => 'All statuses',
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search name or registration" aria-label="Search companies">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">Company</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Status</th>
                            <th scope="col">Compliance</th>
                            <th scope="col">Job posts</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            @php
                                $verified = $company->complianceRecords
                                    ->where('status', \App\Models\Compliance::STATUS_VERIFIED)->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($company->logoUrl())
                                                <img src="{{ $company->logoUrl() }}" alt="">
                                            @else
                                                {{ $company->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                {{ $company->name }}
                                                @if ($verified > 0)
                                                    <x-verified-badge :show-label="false" :size="16" />
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">{{ $company->registration_no ?: 'No registration number' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $company->email ?: '—' }}
                                    <span class="kh-bo__ref">{{ $company->phone ?: 'No phone' }}</span>
                                </td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $company->status === 'approved' ? 'verified' : ($company->status === 'pending' ? 'pending' : 'rejected') }}">
                                        {{ ucfirst($company->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($company->compliance_records_count === 0)
                                        <span class="kh-bo__expiry-flag kh-bo__expiry-flag--past">None on file</span>
                                    @else
                                        {{ $verified }}/{{ $company->compliance_records_count }} verified
                                        <span class="kh-bo__ref">
                                            <a href="{{ route('compliance.index', ['q' => $company->name]) }}">View records</a>
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $company->job_posts_count }}
                                    <span class="kh-bo__ref">
                                        <a href="{{ route('job-posts.index', ['q' => $company->name]) }}">View posts</a>
                                    </span>
                                </td>

                                <td>
                                    @if (auth()->user()?->isAdmin())
                                        <div class="kh-bo__actions">
                                            <a class="kh-bo__action" href="{{ route('companies.edit', $company) }}"
                                                title="Edit company" aria-label="Edit {{ $company->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                                </svg>
                                            </a>

                                            <form method="POST" action="{{ route('companies.destroy', $company) }}"
                                                onsubmit="return confirm('Delete {{ addslashes($company->name) }}? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                    title="Delete company" aria-label="Delete {{ $company->name }}">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="kh-bo__empty">
                                        <strong>No companies</strong>
                                        <span>
                                            @if ($activeStatus || $searchTerm)
                                                Nothing matches this filter.
                                                <a href="{{ route('companies') }}">Clear it</a> to see everything.
                                            @else
                                                Add the first employer to start posting jobs against it.
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
