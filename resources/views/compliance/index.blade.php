@extends('layouts.admin')

@section('title', 'Compliance | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $records->count();
        $verified = $counts['verified'] ?? 0;
        $pending = $counts['pending'] ?? 0;
        $rejected = $counts['rejected'] ?? 0;

        $filters = [
            '' => 'All',
            'verified' => 'Verified',
            'pending' => 'Pending',
            'rejected' => 'Rejected',
        ];
    @endphp

    <div class="kh-bo">
        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Back office</span>
                <h1>Compliance</h1>
                <p>Track the licences and certificates each employer has to hold, and sign them off.</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('compliance.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Add record
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

        <section class="kh-bo__tiles" aria-label="Compliance summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>Records</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div><strong>{{ number_format($verified) }}</strong><span>Verified</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($pending) }}</strong><span>Awaiting review</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($rejected) }}</strong><span>Rejected</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Compliance register</h2>
                    <p>{{ $total }} {{ \Illuminate\Support\Str::plural('record', $total) }} shown.</p>
                </div>

                <div class="kh-bo__tools">
                    <div class="kh-bo__filters">
                        @foreach ($filters as $value => $label)
                            <a class="kh-bo__filter{{ (string) $activeStatus === (string) $value ? ' is-active' : '' }}"
                                href="{{ route('compliance.index', array_filter(['status' => $value, 'q' => $searchTerm])) }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form class="kh-bo__search" method="GET" action="{{ route('compliance.index') }}" role="search">
                        @if ($activeStatus)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search name or reference" aria-label="Search compliance records">
                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">Organisation</th>
                            <th scope="col">Category</th>
                            <th scope="col">Status</th>
                            <th scope="col">Expires</th>
                            <th scope="col">Verified by</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $record)
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($record->logoUrl())
                                                <img src="{{ $record->logoUrl() }}" alt="">
                                            @else
                                                {{ $record->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">
                                                {{ $record->name }}
                                                @if ($record->isVerified())
                                                    <x-verified-badge :show-label="false" :size="16" />
                                                @endif
                                            </span>
                                            <span class="kh-bo__ref">{{ $record->reference ?: 'No reference' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $record->category }}</td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $record->status }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($record->expires_on)
                                        {{ $record->expires_on->format('M j, Y') }}
                                        @if ($record->hasExpired())
                                            <span class="kh-bo__expiry-flag kh-bo__expiry-flag--past">Expired</span>
                                        @elseif ($record->expiresSoon())
                                            <span class="kh-bo__expiry-flag">Expiring soon</span>
                                        @endif
                                    @else
                                        <span class="kh-bo__ref">No expiry</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($record->isVerified())
                                        <x-verified-badge :label="$record->verifier?->displayName() ?? 'Verified'" />
                                        <span class="kh-bo__ref">{{ $record->verified_at?->format('M j, Y') }}</span>
                                    @else
                                        <span class="kh-bo__ref">—</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="kh-bo__actions">
                                        <form method="POST" action="{{ route('compliance.verify', $record) }}">
                                            @csrf
                                            <button
                                                class="kh-bo__action kh-bo__action--verify{{ $record->isVerified() ? ' is-on' : '' }}"
                                                type="submit"
                                                title="{{ $record->isVerified() ? 'Remove verification' : 'Mark as verified' }}"
                                                aria-label="{{ $record->isVerified() ? 'Remove verification from' : 'Mark as verified:' }} {{ $record->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </button>
                                        </form>

                                        <a class="kh-bo__action" href="{{ route('compliance.edit', $record) }}"
                                            title="Edit record" aria-label="Edit {{ $record->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('compliance.destroy', $record) }}"
                                            onsubmit="return confirm('Delete the compliance record for {{ addslashes($record->name) }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="Delete record" aria-label="Delete {{ $record->name }}">
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
                                        <strong>Nothing to review</strong>
                                        <span>
                                            @if ($activeStatus || $searchTerm)
                                                No records match this filter.
                                                <a href="{{ route('compliance.index') }}">Clear it</a> to see everything.
                                            @else
                                                Add the first compliance record to get started.
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
