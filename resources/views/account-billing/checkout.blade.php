@extends('layouts.admin')

@section('title', 'Confirm billing | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
    <link href="{{ asset('css/billing.css') }}?v={{ filemtime(public_path('css/billing.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    @php
        $isAnnual = $billingPeriod === 'annual';
        $isFree = $pricing['due_today'] <= 0;
        $saving = round($pricing['undiscounted'] - $pricing['due_today'], 2);

        // Always two decimals: this is the screen where the figure has to
        // match what the bank app will show, and "$1" vs "$1.00" invites a
        // second look at a moment that should be certain.
        $money = fn (float $value) => '$' . number_format($value, 2);
        $blocked = ! $isFree && ! $payWayConfigured;
    @endphp

    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="{{ __('ui.admin.a11y.breadcrumb') }}">
            <a href="{{ route('home') }}">{{ __('ui.bo.breadcrumb_root') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('account-billing') }}">{{ __('ui.bo.billing.title') }}</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">{{ __('ui.bo.billing.confirm_title') }}</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">{{ __('ui.bo.billing.kicker') }}</span>
                <h1>{{ __('ui.bo.billing.confirm_title') }}</h1>
                <p>{{ __('ui.bo.billing.confirm_subtitle') }}</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('account-billing') }}">{{ __('ui.bo.billing.back_to_packages') }}</a>
        </header>

        @if ($errors->any())
            <div class="kh-bo__errors" role="alert">
                <strong>{{ __('ui.bo.check_fields') }}</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($blocked)
            <div class="kh-bo__errors" role="alert">
                <strong>{{ __('ui.bo.billing.payway_missing') }}</strong>
                {{ __('ui.bo.billing.payway_missing_body') }}
            </div>
        @endif

        {{-- Two panes: what you're paying on the left, what you're getting on
             the right. The order pane is the one that has to be unambiguous,
             so it carries the totals, the button and the payment method, and
             it leads in the markup so reading order matches the layout. --}}
        <div class="kh-co">
            <section class="kh-co__order" aria-labelledby="kh-co-order-title">
                <header class="kh-co__order-head">
                    <span class="kh-co__order-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="17" rx="2.5" /><path d="M9 4V3h6v1" /><path d="M9 10h6M9 14h3" /></svg>
                    </span>

                    <div>
                        <h2 class="kh-co__order-title" id="kh-co-order-title">{{ __('ui.bo.billing.order_summary') }}</h2>
                        <p class="kh-co__order-sub">{{ __('ui.bo.billing.order_review') }}</p>
                    </div>
                </header>

                <dl class="kh-co__rows">
                    <div class="kh-co__row">
                        <span class="kh-co__row-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z" /><path d="M4 7.5l8 4.5 8-4.5M12 12v9" /></svg>
                        </span>
                        <dt>{{ __('ui.bo.billing.package') }}</dt>
                        <dd>{{ __('ui.bo.billing.plan.names.' . $plan['name']) }}</dd>
                    </div>

                    <div class="kh-co__row">
                        <span class="kh-co__row-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="16" rx="2.5" /><path d="M3.5 10h17M8 3v4M16 3v4" /><path d="M8 14h.01M12 14h.01M16 14h.01" /></svg>
                        </span>
                        <dt>{{ __('ui.bo.billing.period_label') }}</dt>
                        <dd>
                            {{ $isAnnual ? 'Annual' : 'Monthly' }}
                            <a class="kh-co__change" href="{{ route('account-billing') }}">
                                {{ __('ui.bo.billing.change') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
                            </a>
                        </dd>
                    </div>

                    @unless ($isFree)
                        {{-- The list rate, so this row's arithmetic reads
                             straight: rate x months = the amount beside it,
                             with the discount taken off on the next row. --}}
                        @if ($pricing['months'] > 1)
                            <div class="kh-co__row">
                                <span class="kh-co__row-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2.5" /><path d="M9 8h6M9 12h6M9 16h3" /></svg>
                                </span>
                                <dt>{{ $money((float) $plan['monthly']) }} / month &times; {{ $pricing['months'] }}</dt>
                                <dd>{{ $money($pricing['undiscounted']) }}</dd>
                            </div>
                        @endif

                        @if ($saving > 0)
                            <div class="kh-co__row kh-co__row--saving">
                                <span class="kh-co__row-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 13.5l-7 7a2 2 0 01-2.8 0l-7.2-7.2V4h9.3z" /><path d="M9 8.5h.01" /></svg>
                                </span>
                                <dt>Annual discount ({{ (int) (config('plans.annual_discount') * 100) }}%)</dt>
                                <dd>&minus;{{ $money($saving) }}</dd>
                            </div>
                        @endif
                    @endunless
                </dl>

                <div class="kh-co__totalcard">
                    <span class="kh-co__total-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><path d="M14.5 9.2c-.5-.8-1.4-1.2-2.5-1.2-1.4 0-2.4.7-2.4 1.8 0 2.5 5 1.3 5 3.9 0 1.2-1.1 2-2.6 2-1.2 0-2.2-.5-2.6-1.4M12 6.4v11.2" /></svg>
                    </span>

                    <div class="kh-co__total-text">
                        <strong>{{ __('ui.bo.billing.total_due') }}</strong>
                        <span>
                            @if ($isFree)
                                No card needed — this plan stays free.
                            @elseif ($isAnnual)
                                Covers 12 months — {{ $money($pricing['per_month']) }} / month. Renews {{ now()->addYear()->format('j M Y') }}.
                            @else
                                Renews {{ now()->addMonth()->format('j M Y') }} at {{ $money($pricing['due_today']) }}.
                            @endif
                        </span>
                    </div>

                    <span class="kh-co__total-figure">{{ $isFree ? 'Free' : $money($pricing['due_today']) }}</span>
                </div>

                <form method="POST" action="{{ route('account-billing.pay') }}" data-checkout-form>
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                    <input type="hidden" name="billing_period" value="{{ $billingPeriod }}">

                    <button class="kh-co__submit" type="submit" data-checkout-submit {{ $blocked ? 'disabled' : '' }}>
                        @unless ($isFree)
                            <svg class="kh-co__submit-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2.5" /><path d="M8.5 10.5V7.8a3.5 3.5 0 017 0v2.7" /></svg>
                        @endunless

                        <span>{{ $isFree ? 'Confirm and subscribe' : 'Pay ' . $money($pricing['due_today']) }}</span>

                        <svg class="kh-co__submit-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h15M13 6l6 6-6 6" /></svg>
                    </button>

                    <a class="kh-co__cancel" href="{{ route('account-billing') }}">{{ __('ui.bo.job_posts.form.cancel') }}</a>
                </form>

                @unless ($isFree)
                    <div class="kh-co__method">
                        @include('partials.khqr-mark')

                        <div class="kh-co__method-text">
                            <strong>{{ __('ui.bo.billing.khqr') }}</strong>
                            <span>{{ __('ui.bo.billing.khqr_hint') }}</span>
                            <span class="kh-co__secured">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5.5c0 4.2-2.9 7.6-7 9-4.1-1.4-7-4.8-7-9V6z" /><path d="M9 12l2.2 2.2L15.5 10" /></svg>
                                {{ __('ui.bo.billing.khqr_secured') }}
                            </span>
                        </div>
                    </div>
                @endunless
            </section>

            <section class="kh-co__plan kh-co__plan--{{ $plan['tone'] }}" aria-labelledby="kh-co-plan-name">
                <header class="kh-co__plan-head">
                    <span class="kh-co__plan-icon" aria-hidden="true">
                        <i class="fas {{ $plan['icon'] }}"></i>
                    </span>

                    <div>
                        <h2 class="kh-co__plan-name" id="kh-co-plan-name">{{ __('ui.bo.billing.plan.names.' . $plan['name']) }}</h2>
                        <p class="kh-co__plan-tagline">{{ __('ui.bo.billing.plan.taglines.' . $plan['tagline']) }}</p>
                    </div>
                </header>

                <p class="kh-co__plan-blurb">{{ $plan['blurb'] }}</p>

                <p class="kh-co__plan-included">{{ __('ui.bo.billing.included') }}</p>

                <ul class="kh-co__features">
                    @foreach ($plan['features'] as $feature)
                        <li class="{{ $feature['highlight'] ? 'is-highlight' : '' }}">
                            <span class="kh-co__tick" aria-hidden="true"><i class="fas fa-check"></i></span>
                            {{ __('ui.bo.billing.plan.features.' . $feature['label']) }}
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </div>
@endsection
