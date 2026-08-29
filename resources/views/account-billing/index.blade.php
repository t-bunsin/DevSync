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
                    'note' => $monthly === 0 ? __('ui.bo.billing.free_forever') : __('ui.bo.billing.billed_monthly'),
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
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.billing.title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">{{ __('ui.bo.billing.kicker') }}</span>
                <h1>{{ __('ui.bo.billing.title') }}</h1>
                <p>{{ __('ui.bo.billing.subtitle') }}</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('home') }}">{{ __('ui.bo.billing.back_to_dashboard') }}</a>
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
                    <strong>{{ __('ui.bo.billing.on_plan', ['plan' => __('ui.bo.billing.plan.names.' . ($subscription->plan()['name'] ?? $subscription->plan_id))]) }}</strong>
                    <span>
                        ${{ number_format($subscription->amount, 0) }} / {{ $subscription->billing_period === 'annual' ? 'month, billed annually' : 'month' }}
                        &middot; since {{ $subscription->started_at->format('M j, Y') }}
                    </span>
                </div>
            </div>
        @elseif ($subscription && $subscription->status === 'pending')
            <div class="kh-bo__errors" role="status">
                <strong>{{ __('ui.bo.billing.payment_pending') }}</strong>
                {{ __('ui.bo.billing.awaiting_payway', ['plan' => __('ui.bo.billing.plan.names.' . ($subscription->plan()['name'] ?? $subscription->plan_id))]) }}
            </div>
        @elseif ($subscription && $subscription->status === 'failed')
            <div class="kh-bo__errors" role="status">
                <strong>{{ __('ui.bo.billing.payment_failed') }}</strong>
                {{ __('ui.bo.billing.payment_failed_plan', ['plan' => __('ui.bo.billing.plan.names.' . ($subscription->plan()['name'] ?? $subscription->plan_id))]) }}
            </div>
        @endif

        <div class="kh-bill" data-period="monthly">
            <div class="kh-bill__intro">
                <span class="kh-bill__intro-kicker">{{ __('ui.bo.billing.choose_plan') }}</span>
                <h2>{{ __('ui.bo.billing.pricing_heading') }}</h2>
                <p>{{ __('ui.bo.billing.pricing_lead') }}</p>

                <div class="kh-bill__period" data-billing-toggle role="group" aria-label="{{ __('ui.bo.billing.period_label') }}">
                    <button type="button" data-period="monthly" aria-pressed="true">{{ __('ui.bo.billing.monthly') }}</button>
                    <button type="button" data-period="annual" aria-pressed="false">
                        {{ __('ui.bo.billing.annual') }}<span>Save {{ round($annualDiscount * 100) }}%</span>
                    </button>
                </div>
            </div>

            <div class="kh-bill__grid">
                @foreach ($tiers as $plan)
                    @php $isCurrent = $subscription?->status === 'active' && $subscription->plan_id === $plan['id']; @endphp

                    {{-- Each tier carries its own accent, so the icon, ticks and
                         button all pick up one colour per card. --}}
                    <article @class([
                        'kh-bill__plan',
                        'kh-bill__plan--' . $plan['tone'],
                        'kh-bill__plan--featured' => $plan['featured'],
                        'kh-bill__plan--current' => $isCurrent,
                    ])>
                        @if ($isCurrent)
                            <span class="kh-bill__plan-badge">
                                <i class="fas fa-circle-check" aria-hidden="true"></i> {{ __('ui.bo.billing.current_plan') }}
                            </span>
                        @elseif ($plan['featured'])
                            <span class="kh-bill__plan-badge">
                                <i class="far fa-star" aria-hidden="true"></i> {{ __('ui.bo.billing.most_popular') }}
                            </span>
                        @endif

                        <div class="kh-bill__plan-head">
                            <span class="kh-bill__plan-icon" aria-hidden="true">
                                <i class="fas {{ $plan['icon'] }}"></i>
                            </span>
                            <div>
                                <h3 class="kh-bill__plan-name">{{ __('ui.bo.billing.plan.names.' . $plan['name']) }}</h3>
                                <p class="kh-bill__plan-tagline">{{ __('ui.bo.billing.plan.taglines.' . $plan['tagline']) }}</p>
                            </div>
                        </div>

                        <p class="kh-bill__plan-price">
                            <span class="kh-bill__plan-amount" data-amount
                                data-monthly="{{ $plan['amounts']['monthly']['price'] }}"
                                data-annual="{{ $plan['amounts']['annual']['price'] }}">{{ $plan['amounts']['monthly']['price'] }}</span>
                            <span class="kh-bill__plan-cycle">{{ __('ui.bo.billing.per_month') }}</span>
                        </p>

                        <p class="kh-bill__plan-blurb">{{ __('ui.bo.billing.plan.blurbs.' . $plan['blurb']) }}</p>

                        <p class="kh-bill__plan-note" data-amount data-monthly="{{ $plan['amounts']['monthly']['note'] }}"
                            data-annual="{{ $plan['amounts']['annual']['note'] }}">{{ $plan['amounts']['monthly']['note'] }}</p>

                        <p class="kh-bill__plan-included">{{ __('ui.bo.billing.included') }}</p>

                        <ul class="kh-bill__plan-features">
                            @foreach ($plan['features'] as $feature)
                                <li @class(['is-highlight' => $feature['highlight']])>
                                    <span class="kh-bill__plan-tick" aria-hidden="true"><i class="fas fa-check"></i></span>
                                    <span>{{ __('ui.bo.billing.plan.features.' . $feature['label']) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a class="kh-bill__plan-select" data-plan-select
                            href="{{ route('account-billing.checkout', ['plan_id' => $plan['id'], 'billing_period' => 'monthly']) }}">
                            {{ $isCurrent ? __('ui.bo.billing.change_period') : __('ui.bo.billing.select_plan', ['plan' => __('ui.bo.billing.plan.names.' . $plan['name'])]) }}
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </article>
                @endforeach
            </div>

            <p class="kh-bill__foot">
                <i class="fas fa-shield-halved" aria-hidden="true"></i>
                {!! __('ui.bo.billing.footnote') !!}
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/billing.js') }}?v={{ filemtime(public_path('js/billing.js')) }}"></script>
@endpush
