@extends('layouts.admin')

@section('title', 'Redirecting to PayWay | KH-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    <div class="kh-bo">
        <nav class="kh-bo__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Back office</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <a href="{{ route('account-billing') }}">Billing</a>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
            <span aria-current="page">Redirecting to PayWay</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Account</span>
                <h1>Redirecting to PayWay&hellip;</h1>
                <p>Scan the KHQR code with your banking app to finish paying.</p>
            </div>
        </header>

        <div class="kh-bo__form-card" style="text-align:center; padding: 40px 24px;">
            <i class="fas fa-circle-notch fa-spin" aria-hidden="true" style="font-size: 1.6rem; color: var(--kh-ocean);"></i>
            <p style="margin-top: 14px; color: var(--kh-muted);">
                If you're not redirected automatically,
                <button type="submit" form="payway-form" class="kh-bo__btn kh-bo__btn--ghost" style="display:inline; padding: 2px 10px;">click here</button>.
            </p>
        </div>

        <form id="payway-form" method="POST" action="{{ $purchaseUrl }}">
            @foreach ($fields as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('payway-form').submit();
    </script>
@endpush
