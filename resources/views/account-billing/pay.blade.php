@extends('layouts.admin')

@section('title', 'Scan to pay | KH-WORKS Admin')

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
            <span aria-current="page">Scan to pay</span>
        </nav>

        <header class="kh-bo__head">
            <div>
                <span class="kh-bo__kicker">Account</span>
                <h1>Scan to pay</h1>
                <p>Open your banking app and scan the KHQR code to finish paying.</p>
            </div>
        </header>

        <div class="kh-bo__form-card kh-pay" data-tran-id="{{ $tranId }}"
            data-status-url="{{ route('account-billing.payway.status') }}"
            data-success-url="{{ route('account-billing.payway.return') }}"
            data-checkout-url="{{ route('account-billing') }}"
            data-expires-at="{{ $expiresAt }}">

            <div class="kh-pay__badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><path d="M14 14h3v3h-3zM20 14v.01M14 20h.01M17 17h3v3h-3z" /></svg>
                Bakong KHQR
            </div>

            <div data-live>
                @if ($qrImage)
                    <img class="kh-pay__qr" src="{{ $qrImage }}" alt="KHQR code — scan with your banking app to pay">
                @else
                    <p class="kh-pay__missing">PayWay didn't return a QR code for this payment. Please try again.</p>
                @endif

                <p class="kh-pay__expiry" data-expiry></p>

                @if ($deeplink)
                    <a class="kh-bo__btn kh-pay__deeplink" href="{{ $deeplink }}">Open ABA Mobile</a>
                @endif

                @if ($appStore || $playStore)
                    <p class="kh-pay__stores-label">Don't have the app yet?</p>
                    <div class="kh-pay__stores">
                        @if ($appStore)
                            <a href="{{ $appStore }}" target="_blank" rel="noopener">App Store</a>
                        @endif
                        @if ($playStore)
                            <a href="{{ $playStore }}" target="_blank" rel="noopener">Google Play</a>
                        @endif
                    </div>
                @endif

                <p class="kh-pay__waiting" data-waiting>
                    <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                    Waiting for payment confirmation&hellip;
                </p>
                <p class="kh-pay__failed" data-failed hidden>
                    That payment didn't go through. <a href="{{ route('account-billing') }}">Try again</a>.
                </p>
            </div>

            <p class="kh-pay__expired" data-expired hidden>
                This code has expired. <a href="{{ route('account-billing') }}">Start a new payment</a>.
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const card = document.querySelector('.kh-pay');
            if (!card) return;

            const tranId = card.dataset.tranId;
            const statusUrl = card.dataset.statusUrl;
            const successUrl = card.dataset.successUrl;
            const expiresAt = Number(card.dataset.expiresAt) * 1000;
            const live = card.querySelector('[data-live]');
            const expiryLabel = card.querySelector('[data-expiry]');
            const waiting = card.querySelector('[data-waiting]');
            const failed = card.querySelector('[data-failed]');
            const expired = card.querySelector('[data-expired]');

            let pollTimer = null;
            let expiredAlready = false;

            const poll = async () => {
                if (expiredAlready) return;

                try {
                    const response = await fetch(`${statusUrl}?tran_id=${encodeURIComponent(tranId)}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();

                    if (data.status === 'active') {
                        window.location.href = successUrl;
                        return;
                    }

                    if (data.status === 'failed') {
                        waiting.hidden = true;
                        failed.hidden = false;
                        return;
                    }
                } catch (error) {
                    // Network hiccup — the next tick tries again.
                }

                pollTimer = setTimeout(poll, 4000);
            };

            const tickCountdown = () => {
                const remainingMs = expiresAt - Date.now();

                if (remainingMs <= 0) {
                    expiredAlready = true;
                    if (pollTimer) clearTimeout(pollTimer);
                    live.hidden = true;
                    expired.hidden = false;
                    return;
                }

                const totalSeconds = Math.ceil(remainingMs / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                expiryLabel.textContent = `Expires in ${minutes}:${String(seconds).padStart(2, '0')}`;

                setTimeout(tickCountdown, 1000);
            };

            tickCountdown();
            pollTimer = setTimeout(poll, 4000);
        })();
    </script>
@endpush
