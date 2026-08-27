@extends('layouts.admin')

@section('title', 'Billing list | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $total = $subscriptions->count();

        $filters = ['' => 'All'] + collect(\App\Models\Subscription::STATUSES)
            ->mapWithKeys(fn ($status) => [$status => ucfirst($status)])
            ->all();

        // The register's four statuses reuse the three pill styles backoffice.css
        // already ships, rather than adding near-duplicate colours: canceled and
        // failed both read as "not collecting", so both borrow the rejected tone.
        $statusTone = [
            \App\Models\Subscription::STATUS_ACTIVE => 'verified',
            \App\Models\Subscription::STATUS_PENDING => 'pending',
            \App\Models\Subscription::STATUS_FAILED => 'rejected',
            \App\Models\Subscription::STATUS_CANCELED => 'rejected',
        ];

        $isFiltered = $activeStatus || $searchTerm;
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Billing list</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <h1>Billing list</h1>
                <p>Every account's subscription, the plan it is on and where its last payment got to.</p>
            </div>

            <a class="kh-bo__btn" href="{{ route('account-billing') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" />
                </svg>
                My billing
            </a>
        </header>

        <section class="kh-bo__tiles" aria-label="Billing summary">
            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2" /><path d="M2 10h20" />
                    </svg>
                </span>
                <div>
                    <strong>{{ $counts->sum() }}</strong>
                    <span>Subscriptions</span>
                </div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="9" />
                    </svg>
                </span>
                <div>
                    <strong>{{ $counts[\App\Models\Subscription::STATUS_ACTIVE] ?? 0 }}</strong>
                    <span>Active</span>
                </div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                    </svg>
                </span>
                <div>
                    <strong>{{ $counts[\App\Models\Subscription::STATUS_PENDING] ?? 0 }}</strong>
                    <span>Awaiting payment</span>
                </div>
            </article>

            <article class="kh-bo__tile">
                <span class="kh-bo__tile-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20" /><path d="M17 6H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
                    </svg>
                </span>
                <div>
                    <strong>${{ number_format($activeValue, 2) }}</strong>
                    <span>Active plan value</span>
                </div>
            </article>
        </section>

        <section class="kh-bo__card" aria-label="Subscriptions">
            <div class="kh-bo__card-head">
                <div>
                    <h2>Subscriptions</h2>
                    <p>{{ $total }} {{ \Illuminate\Support\Str::plural('subscription', $total) }} shown.</p>
                </div>

                <div class="kh-bo__tools">
                    <form class="kh-bo__search" method="GET" action="{{ route('account-billing.list') }}" role="search">
                        @include('partials.kh-bo-filter-select', [
                            'name' => 'status',
                            'options' => $filters,
                            'active' => $activeStatus,
                            'label' => 'Filter by status',
                            'allLabel' => 'All statuses',
                        ])

                        <input type="search" name="q" value="{{ $searchTerm }}"
                            placeholder="Search name, email or transaction" aria-label="Search subscriptions">

                        <button class="kh-bo__btn kh-bo__btn--ghost" type="submit">Search</button>
                        @if ($isFiltered)
                            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('account-billing.list') }}">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kh-bo__table-wrap">
                <table class="kh-bo__table">
                    <thead>
                        <tr>
                            <th scope="col">Account</th>
                            <th scope="col">Plan</th>
                            <th scope="col">Billing</th>
                            <th scope="col">Amount</th>
                            <th scope="col">Status</th>
                            <th scope="col">Transaction</th>
                            <th scope="col">Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $subscription)
                            <tr>
                                <td>
                                    <div class="kh-bo__identity">
                                        <span class="kh-bo__logo" aria-hidden="true">
                                            @if ($subscription->user?->avatarUrl())
                                                <img src="{{ $subscription->user->avatarUrl() }}" alt="">
                                            @else
                                                {{ $subscription->user?->initials() ?? '?' }}
                                            @endif
                                        </span>
                                        <div>
                                            <span class="kh-bo__name">{{ $subscription->user?->displayName() ?? 'Deleted account' }}</span>
                                            <span class="kh-bo__ref">{{ $subscription->user?->email ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- plan() reads config/plans.php, so a tier removed from
                                     the config still shows the id it was sold under. --}}
                                <td>{{ $subscription->plan()['name'] ?? $subscription->plan_id }}</td>

                                <td>{{ $subscription->billing_period === 'annual' ? 'Annual' : 'Monthly' }}</td>

                                <td>${{ number_format($subscription->amount, 2) }}</td>

                                <td>
                                    <span class="kh-bo__status kh-bo__status--{{ $statusTone[$subscription->status] ?? 'pending' }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if ($subscription->tran_id)
                                        <span class="kh-bo__ref">{{ $subscription->tran_id }}</span>
                                    @else
                                        <span class="kh-bo__ref">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($subscription->started_at)
                                        {{ $subscription->started_at->format('M j, Y') }}
                                    @else
                                        <span class="kh-bo__ref">Not started</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="kh-bo__empty">
                                        <strong>No subscriptions yet</strong>
                                        <span>
                                            @if ($isFiltered)
                                                No subscriptions match this filter.
                                                <a href="{{ route('account-billing.list') }}">Clear it</a> to see everything.
                                            @else
                                                Nobody has bought a plan yet. They will appear here as soon as someone checks out.
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
