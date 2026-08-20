@extends('layouts.admin')

@section('title', 'Billing | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
    <link href="{{ asset('css/billing.css') }}?v={{ filemtime(public_path('css/billing.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $annualDiscount = config('plans.annual_discount');

        $tiers = array_map(function (array $plan) use ($annualDiscount) {
            $monthly = $plan['monthly'];
            $annual = (int) round($monthly * (1 - $annualDiscount));

            $plan['amounts'] = [
                'monthly' => [
                    'price' => '$' . number_format($monthly),
                    'note' => $monthly === 0 ? 'Free forever' : 'Billed monthly',
                ],
                'annual' => [
                    'price' => '$' . number_format($annual),
                    'note' => $monthly === 0 ? 'Free forever' : 'Billed $' . number_format($annual * 12) . ' per year',
                ],
            ];

            return $plan;
        }, $tiers);
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Billing</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Account</span>
                <h1>Billing</h1>
                <p>Choose the package that fits your job board.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('home') }}">Back to dashboard</a>
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

        @if ($subscription && $subscription->status === 'active')
            <div class="kh-bill__current">
                <div>
                    <strong>You're on the {{ $subscription->plan()['name'] ?? $subscription->plan_id }} plan</strong>
                    <span>
                        ${{ number_format($subscription->amount, 0) }} / {{ $subscription->billing_period === 'annual' ? 'month, billed annually' : 'month' }}
                        &middot; since {{ $subscription->started_at->format('M j, Y') }}
                    </span>
                </div>
            </div>
        @elseif ($subscription && $subscription->status === 'pending')
            <div class="kh-bo__errors" role="status">
                <strong>Payment pending.</strong>
                We're waiting for PayWay to confirm your {{ $subscription->plan()['name'] ?? $subscription->plan_id }} plan payment. Refresh this page in a moment.
            </div>
        @elseif ($subscription && $subscription->status === 'failed')
            <div class="kh-bo__errors" role="status">
                <strong>Last payment failed.</strong>
                Your {{ $subscription->plan()['name'] ?? $subscription->plan_id }} plan payment didn't go through. Pick a package below to try again.
            </div>
        @endif

        <div class="kh-bill" data-period="monthly">
            <div class="kh-bill__period" data-billing-toggle>
                <button type="button" data-period="monthly" aria-pressed="true">Monthly</button>
                <button type="button" data-period="annual" aria-pressed="false">
                    Annual<span>Save {{ round($annualDiscount * 100) }}%</span>
                </button>
            </div>

            <div class="kh-bill__grid">
                @foreach ($tiers as $plan)
                    @php $isCurrent = $subscription?->status === 'active' && $subscription->plan_id === $plan['id']; @endphp

                    <article @class([
                        'kh-bill__plan',
                        'kh-bill__plan--featured' => $plan['featured'],
                        'kh-bill__plan--current' => $isCurrent,
                    ])>
                        @if ($isCurrent)
                            <span class="kh-bill__plan-badge">Current plan</span>
                        @elseif ($plan['featured'])
                            <span class="kh-bill__plan-badge">Most popular</span>
                        @endif

                        <h3 class="kh-bill__plan-name">{{ $plan['name'] }}</h3>

                        <p class="kh-bill__plan-price">
                            <span data-amount data-monthly="{{ $plan['amounts']['monthly']['price'] }}"
                                data-annual="{{ $plan['amounts']['annual']['price'] }}">{{ $plan['amounts']['monthly']['price'] }}</span>
                            <span>/month</span>
                        </p>

                        <p class="kh-bill__plan-note" data-amount data-monthly="{{ $plan['amounts']['monthly']['note'] }}"
                            data-annual="{{ $plan['amounts']['annual']['note'] }}">{{ $plan['amounts']['monthly']['note'] }}</p>

                        <p class="kh-bill__plan-blurb">{{ $plan['blurb'] }}</p>

                        <ul class="kh-bill__plan-features">
                            @foreach ($plan['features'] as $feature)
                                <li @class(['is-highlight' => $feature['highlight']])>
                                    <i class="fas fa-check" aria-hidden="true"></i>
                                    <span>{{ $feature['label'] }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a @class([
                            'kh-bo__btn',
                            'kh-bill__plan-select',
                            'kh-bo__btn--ghost' => ! $plan['featured'] && ! $isCurrent,
                        ]) data-plan-select
                            href="{{ route('account-billing.checkout', ['plan_id' => $plan['id'], 'billing_period' => 'monthly']) }}">
                            {{ $isCurrent ? 'Change billing period' : 'Select ' . $plan['name'] }}
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/billing.js') }}?v={{ filemtime(public_path('js/billing.js')) }}"></script>
@endpush
