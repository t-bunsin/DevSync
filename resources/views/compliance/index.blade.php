@extends('layouts.admin')

@section('title', 'Compliance | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/compliance.css') }}?v={{ filemtime(public_path('css/compliance.css')) }}" rel="stylesheet" />
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

    <div class="kh-comp">
        <header class="kh-comp__head">
            <div>
                <span class="kh-comp__kicker">Back office</span>
                <h1>Compliance</h1>
                <p>Track the licences and certificates each employer has to hold, and sign them off.</p>
            </div>

            <a class="kh-comp__btn" href="{{ route('compliance.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Add record
            </a>
        </header>

        @if (session('success'))
            <div class="kh-comp__flash" role="status">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6L9 17l-5-5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <section class="kh-comp__tiles" aria-label="Compliance summary">
            <article class="kh-comp__tile">
                <span class="kh-comp__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>Records</span></div>
            </article>

            <article class="kh-comp__tile">
                <span class="kh-comp__tile-icon kh-comp__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div><strong>{{ number_format($verified) }}</strong><span>Verified</span></div>
            </article>

            <article class="kh-comp__tile">
                <span class="kh-comp__tile-icon kh-comp__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($pending) }}</strong><span>Awaiting review</span></div>
            </article>

            <article class="kh-comp__tile">
                <span class="kh-comp__tile-icon kh-comp__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($rejected) }}</strong><span>Rejected</span></div>
            </article>
        </section>

        <section class="kh-comp__card">
            <div class="kh-comp__card-head">
                <div>
                    <h2>Compliance register</h2>
                    <p>{{ $total }} {{ \Illuminate\Support\Str::plural('record', $total) }} shown.</p>
                </div>

                <div class="kh-comp__tools">
                    <div class="kh-comp__filters">
                        @foreach ($filters as $value => $label)
                            <a class="kh-comp__filter{{ (string) $activeStatus === (string) $value ? ' is-active' : '' }}"
                                href="{{ route('compliance.index', array_filter(['status' => $value, 'q' => $searchTerm])) }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form class="kh-comp__search" method="GET" action="{{ route('compliance.index') }}" role="search">
                        @if ($activeStatus)
                            <input type="hidden" name="status" value="{{ $activeStatus }}">
                        @endif
                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search name or reference" aria-label="Search compliance records">
                        <button class="kh-comp__btn kh-comp__btn--ghost" type="submit">Search</button>
                    </form>
                </div>
            </div>

            <div class="kh-comp__table-wrap">
                <table class="kh-comp__table">
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
                                    <div class="kh-comp__identity">
                                        <span class="kh-comp__logo" aria-hidden="true">
                                            @if ($record->logoUrl())
                                                <img src="{{ $record->logoUrl() }}" alt="">
                                            @else
                                                {{ $record->initials() }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-comp__name">
                                                {{ $record->name }}
                                                @if ($record->isVerified())
                                                    <x-verified-badge :show-label="false" :size="16" />
                                                @endif
                                            </span>
                                            <span class="kh-comp__ref">{{ $record->reference ?: 'No reference' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $record->category }}</td>

                                <td>
                                    <span class="kh-comp__status kh-comp__status--{{ $record->status }}">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($record->expires_on)
                                        {{ $record->expires_on->format('M j, Y') }}
                                        @if ($record->hasExpired())
                                            <span class="kh-comp__expiry-flag kh-comp__expiry-flag--past">Expired</span>
                                        @elseif ($record->expiresSoon())
                                            <span class="kh-comp__expiry-flag">Expiring soon</span>
                                        @endif
                                    @else
                                        <span class="kh-comp__ref">No expiry</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($record->isVerified())
                                        <x-verified-badge :label="$record->verifier?->displayName() ?? 'Verified'" />
                                        <span class="kh-comp__ref">{{ $record->verified_at?->format('M j, Y') }}</span>
                                    @else
                                        <span class="kh-comp__ref">—</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="kh-comp__actions">
                                        <form method="POST" action="{{ route('compliance.verify', $record) }}">
                                            @csrf
                                            <button
                                                class="kh-comp__action kh-comp__action--verify{{ $record->isVerified() ? ' is-on' : '' }}"
                                                type="submit"
                                                title="{{ $record->isVerified() ? 'Remove verification' : 'Mark as verified' }}"
                                                aria-label="{{ $record->isVerified() ? 'Remove verification from' : 'Mark as verified:' }} {{ $record->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </button>
                                        </form>

                                        <a class="kh-comp__action" href="{{ route('compliance.edit', $record) }}"
                                            title="Edit record" aria-label="Edit {{ $record->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('compliance.destroy', $record) }}"
                                            onsubmit="return confirm('Delete the compliance record for {{ addslashes($record->name) }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-comp__action kh-comp__action--danger" type="submit"
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
                                    <div class="kh-comp__empty">
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
