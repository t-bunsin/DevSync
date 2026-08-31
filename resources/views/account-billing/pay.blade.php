@extends('layouts.admin')

@section('title', 'Scan to pay | ZIN-WORKS Admin')

@push('styles')
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
    <link href="{{ asset('css/billing.css') }}?v={{ filemtime(public_path('css/billing.css')) }}" rel="stylesheet" />
@endpush

@section('main-content')
    {{-- Laid out like a KHQR merchant standee: patterned card, tagline,
         bracketed code, KHQR mark at the foot. The bank logo, merchant name
         and merchant ID from that format are deliberately absent — this is a
         one-off payment for a signed-in account, not a standing merchant
         placard. The QR comes from our server-side purchase call. --}}
    <div class="kh-payq" role="dialog" aria-modal="true" aria-labelledby="kh-payq-title"
        data-tran-id="{{ $tranId }}"
        data-status-url="{{ route('account-billing.payway.status') }}"
        data-success-url="{{ route('account-billing.payway.return') }}"
        data-cancel-url="{{ route('account-billing') }}"
        data-expires-at="{{ $expiresAt }}"
        data-window="{{ 5 * 60 }}">

        <div class="kh-payq__dialog">
            <span class="kh-payq__rule kh-payq__rule--top" aria-hidden="true"></span>

            <div class="kh-payq__sheet">
                <header class="kh-payq__head">
                    <span class="kh-payq__timer">
                        <svg class="kh-payq__ring" viewBox="0 0 36 36" aria-hidden="true">
                            <circle class="kh-payq__ring-track" cx="18" cy="18" r="16" />
                            <circle class="kh-payq__ring-fill" cx="18" cy="18" r="16" data-ring />
                        </svg>
                        <span class="kh-payq__countdown" data-expiry>5:00</span>
                    </span>

                    <a class="kh-payq__close" href="{{ route('account-billing') }}" aria-label="{{ __('ui.bo.billing.cancel_payment') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </a>
                </header>

                <p class="kh-payq__tagline" id="kh-payq-title">{{ __('ui.bo.billing.scan_pay_done') }}</p>

                <p class="kh-payq__amount">
                    <span class="kh-payq__figure">{{ number_format($amount, 2) }}</span>
                    <span class="kh-payq__currency">USD</span>
                </p>

                <div data-live>
                    @if ($qrImage)
                        <div class="kh-payq__frame">
                            <span class="kh-payq__corner kh-payq__corner--tl" aria-hidden="true"></span>
                            <span class="kh-payq__corner kh-payq__corner--tr" aria-hidden="true"></span>
                            <span class="kh-payq__corner kh-payq__corner--bl" aria-hidden="true"></span>
                            <span class="kh-payq__corner kh-payq__corner--br" aria-hidden="true"></span>
                            <img class="kh-payq__qr" src="{{ $qrImage }}" alt="{{ __('ui.bo.billing.qr_alt') }}">
                        </div>
                    @else
                        <p class="kh-payq__missing">{{ __('ui.bo.billing.no_qr') }}</p>
                    @endif

                    <p class="kh-payq__plan">{{ $planName }}</p>

                    @if ($deeplink)
                        <a class="kh-payq__deeplink" href="{{ $deeplink }}">{{ __('ui.bo.billing.open_aba') }}</a>
                    @endif

                    <p class="kh-payq__waiting" data-waiting>
                        <i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                        {!! __('ui.bo.billing.waiting') !!}
                    </p>

                    <p class="kh-payq__failed" data-failed hidden>
                        {{ __('ui.bo.billing.payment_declined') }} <a href="{{ route('account-billing') }}">{{ __('ui.bo.billing.try_again') }}</a>.
                    </p>

                    @if ($appStore || $playStore)
                        <p class="kh-payq__stores">
                            {{ __('ui.bo.billing.no_app') }}
                            @if ($appStore)
                                <a href="{{ $appStore }}" target="_blank" rel="noopener">App Store</a>
                            @endif
                            @if ($playStore)
                                <a href="{{ $playStore }}" target="_blank" rel="noopener">Google Play</a>
                            @endif
                        </p>
                    @endif
                </div>

                <div class="kh-payq__expired-panel" data-expired hidden>
                    <p class="kh-payq__expired-icon" aria-hidden="true"><i class="fas fa-clock"></i></p>
                    <p class="kh-payq__expired-text">{{ __('ui.bo.billing.expired') }}</p>
                    <a class="kh-payq__deeplink" href="{{ route('account-billing') }}">{{ __('ui.bo.billing.start_new') }}</a>
                </div>

                <footer class="kh-payq__foot">
                    <span class="kh-payq__member">
                        <span>{{ __('ui.bo.billing.member_of') }}</span>
                        <strong>KHQR</strong>
                    </span>
                </footer>

                <span class="kh-payq__swoosh" aria-hidden="true"></span>
            </div>

            <span class="kh-payq__rule kh-payq__rule--bottom" aria-hidden="true"></span>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const modal = document.querySelector('.kh-payq');
            if (!modal) return;

            const tranId = modal.dataset.tranId;
            const statusUrl = modal.dataset.statusUrl;
            const successUrl = modal.dataset.successUrl;
            const expiresAt = Number(modal.dataset.expiresAt) * 1000;
            const windowSeconds = Number(modal.dataset.window);

            const live = modal.querySelector('[data-live]');
            const timer = modal.querySelector('.kh-payq__timer');
            const expiryLabel = modal.querySelector('[data-expiry]');
            const ring = modal.querySelector('[data-ring]');
            const waiting = modal.querySelector('[data-waiting]');
            const failed = modal.querySelector('[data-failed]');
            const expired = modal.querySelector('[data-expired]');

            // Circumference of r=16, so stroke-dashoffset can be driven as a
            // straight fraction of time remaining.
            const circumference = 2 * Math.PI * 16;
            if (ring) {
                ring.style.strokeDasharray = `${circumference}`;
            }

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
                    // A countdown beside "expired" reads as a contradiction.
                    if (timer) timer.hidden = true;
                    return;
                }

                const totalSeconds = Math.ceil(remainingMs / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                expiryLabel.textContent = `${minutes}:${String(seconds).padStart(2, '0')}`;

                if (ring) {
                    const spent = 1 - Math.min(1, totalSeconds / windowSeconds);
                    ring.style.strokeDashoffset = `${circumference * spent}`;
                }

                setTimeout(tickCountdown, 1000);
            };

            tickCountdown();
            pollTimer = setTimeout(poll, 4000);
        })();
    </script>
@endpush
