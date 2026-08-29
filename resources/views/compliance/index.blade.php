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
            '' => __('ui.bo.all'),
            'verified' => __('ui.bo.status.verified'),
            'pending' => __('ui.bo.status.pending'),
            'rejected' => __('ui.bo.status.rejected'),
        ];

        $isFiltered = $activeStatus || $searchTerm || $fromDate || $toDate;
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.compliance.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>{{ __('ui.bo.compliance.title') }}</h1>
                <p>{{ __('ui.bo.compliance.subtitle') }}</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('compliance.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                {{ __('ui.bo.compliance.add') }}
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

        <section class="kh-bo__tiles" aria-label="{{ __('ui.bo.compliance.summary_label') }}">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($counts->sum()) }}</strong><span>{{ __('ui.bo.compliance.tile_records') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--blue" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div><strong>{{ number_format($verified) }}</strong><span>{{ __('ui.bo.compliance.tile_verified') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--amber" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div><strong>{{ number_format($pending) }}</strong><span>{{ __('ui.bo.compliance.tile_awaiting') }}</span></div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon kh-bo__tile-icon--danger" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                </span>
                <div><strong>{{ number_format($rejected) }}</strong><span>{{ __('ui.bo.compliance.tile_rejected') }}</span></div>
            </article>
        </section>

        <section class="kh-bo__card">
            <div class="kh-bo__card-head">
                <div>
                    <h2>{{ __('ui.bo.compliance.register') }}</h2>
                    <p>
                        {{ trans_choice('ui.bo.compliance.records_shown', $total, ['count' => $total]) }}
                        @if ($fromDate && $toDate)
                            {{ __('ui.bo.compliance.expiring_between', ['from' => $fromDate, 'to' => $toDate]) }}
                        @elseif ($fromDate)
                            {{ __('ui.bo.compliance.expiring_from', ['from' => $fromDate]) }}
                        @elseif ($toDate)
                            {{ __('ui.bo.compliance.expiring_to', ['to' => $toDate]) }}
                        @endif
                    </p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('compliance.index') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => __('ui.bo.compliance.filter_status'),
                            'allLabel' => __('ui.bo.compliance.all_statuses'),
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="{{ __('ui.bo.compliance.search_placeholder') }}" aria-label="{{ __('ui.bo.compliance.search_aria') }}">

                        <div class="kh-bo__range">
                            <input type="date" name="from" value="{{ $fromDate }}"
                                aria-label="{{ __('ui.bo.compliance.from_date') }}" title="{{ __('ui.bo.compliance.from_date') }}">
                            <span aria-hidden="true">–</span>
                            <input type="date" name="to" value="{{ $toDate }}"
                                aria-label="{{ __('ui.bo.compliance.to_date') }}" title="{{ __('ui.bo.compliance.to_date') }}">
                        </div>

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">{{ __('ui.bo.search') }}</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('compliance.index') }}">{{ __('ui.bo.clear') }}</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.bo.compliance.col_organisation') }}</th>
                            <th scope="col">{{ __('ui.bo.compliance.col_category') }}</th>
                            <th scope="col">{{ __('ui.bo.compliance.col_status') }}</th>
                            <th scope="col">{{ __('ui.bo.compliance.col_expires') }}</th>
                            <th scope="col">{{ __('ui.bo.compliance.col_verified_by') }}</th>
                            <th scope="col"><span class="visually-hidden">{{ __('ui.bo.actions') }}</span></th>
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

                                <td>{{ __('ui.bo.options.compliance_category.' . $record->category) }}</td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $record->status }}">
                                        {{ __('ui.bo.status.' . $record->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($record->expires_on)
                                        {{ $record->expires_on->format('M j, Y') }}
                                        @if ($record->hasExpired())
                                            <span class="kh-bo__expiry-flag kh-bo__expiry-flag--past">{{ __('ui.bo.compliance.expired') }}</span>
                                        @elseif ($record->expiresSoon())
                                            <span class="kh-bo__expiry-flag">{{ __('ui.bo.compliance.expiring_soon') }}</span>
                                        @endif
                                    @else
                                        <span class="kh-bo__ref">{{ __('ui.bo.compliance.no_expiry') }}</span>
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
                                            title="{{ __('ui.bo.compliance.edit_record') }}" aria-label="{{ __('ui.bo.compliance.edit_aria', ['name' => $record->name]) }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z" />
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('compliance.destroy', $record) }}"
                                            onsubmit="return confirm('{{ addslashes(__('ui.bo.delete_confirm', ['name' => $record->name])) }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kh-bo__action kh-bo__action--danger" type="submit"
                                                title="{{ __('ui.bo.compliance.delete_record') }}" aria-label="{{ __('ui.bo.compliance.delete_aria', ['name' => $record->name]) }}">
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
                                        <strong>{{ __('ui.bo.compliance.empty_title') }}</strong>
                                        <span>
                                            @if ($isFiltered)
                                                {{ __('ui.bo.compliance.empty_filtered') }}
                                                <a href="{{ route('compliance.index') }}">{{ __('ui.bo.clear_it') }}</a> {{ __('ui.bo.to_see_everything') }}
                                            @else
                                                {{ __('ui.bo.compliance.empty_none') }}
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
