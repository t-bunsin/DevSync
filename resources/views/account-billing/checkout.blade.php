@extends('layouts.admin')

@section('title', 'Confirm billing | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
    <link href="{{ asset('css/billing.css') }}?v={{ filemtime(public_path('css/billing.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('account-billing') }}">Billing</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Confirm your plan</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Account</span>
                <h1>Confirm your plan</h1>
                <p>Review the details before switching billing.</p>
            </div>

            <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('account-billing') }}">Back to packages</a>
        </header>

        @if ($errors->any())
            <div class="kh-bo__errors" role="alert">
                <strong>Please check the highlighted fields.</strong>
                <ul>
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($amount > 0 && ! $payWayConfigured)
            <div class="kh-bo__errors" role="alert">
                <strong>Payment isn't set up yet.</strong>
                Ask an administrator to add PAYWAY_MERCHANT_ID and PAYWAY_API_KEY before this plan can be purchased.
            </div>
        @endif

        <div class="kh-bo__form-card kh-bill__checkout">
            <div class="kh-bill__summary">
                <span>Package</span>
                <strong>{{ $plan['name'] }}</strong>
            </div>

            <div class="kh-bill__summary">
                <span>Billing period</span>
                <strong>{{ $billingPeriod === 'annual' ? 'Annual' : 'Monthly' }}</strong>
            </div>

            <div class="kh-bill__summary">
                <span>{{ $billingPeriod === 'annual' ? 'Billed annually at' : 'Billed monthly at' }}</span>
                <strong>${{ number_format($amount, 0) }} {{ $billingPeriod === 'annual' ? 'x 12' : '' }}</strong>
            </div>

            <div class="kh-bill__summary kh-bill__total">
                <span>Total due today</span>
                <strong>${{ number_format($billingPeriod === 'annual' ? $amount * 12 : $amount, 0) }}</strong>
            </div>

            <form method="POST" action="{{ route('account-billing.pay') }}" data-checkout-form>
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
                <input type="hidden" name="billing_period" value="{{ $billingPeriod }}">

                <div class="kh-bo__form-actions">
                    <a class="kh-bo__btn kh-bo__btn--ghost" href="{{ route('account-billing') }}">Cancel</a>
                    <button class="kh-bo__btn" type="submit" data-checkout-submit
                        {{ $amount > 0 && ! $payWayConfigured ? 'disabled' : '' }}>
                        {{ $amount > 0 ? 'Continue to Bakong / KHQR payment' : 'Confirm and subscribe' }}
                    </button>
                </div>

                <p class="kh-bill__processing" data-checkout-status>
                    <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                    {{ $amount > 0 ? 'Redirecting to PayWay…' : 'Processing your billing details…' }}
                </p>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/billing.js') }}?v={{ filemtime(public_path('js/billing.js')) }}"></script>
@endpush
