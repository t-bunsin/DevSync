<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * ABA PayWay checkout (Bakong/KHQR).
 *
 * ============================================================================
 * VERIFICATION STATUS
 * ============================================================================
 * Outbound purchase hash (HASH_FIELD_ORDER) — CONFIRMED 2026-08-26 against
 * the live sandbox (checkout-sandbox.payway.com.kh) with the sandbox merchant
 * id: PayWay answered status.code "00" / "Success!" and returned a real
 * qrString + qrImage. A wrong field order fails closed ("Wrong Hash"), so a
 * success response is positive proof the order is right.
 *
 * Inbound callback signature (verifyCallback) — STILL UNVERIFIED. No callback
 * has been observed, so the concatenation there remains a guess. It fails
 * closed, which is what keeps paywayCallback() from activating a subscription
 * it cannot authenticate. Confirm it against PayWay's merchant API PDF (issued
 * at onboarding) before trusting a callback to grant paid access.
 * ============================================================================
 */
class PayWayService
{
    /**
     * Concatenation order for the outbound purchase hash. Unset/empty fields
     * still occupy their position as an empty string — do not skip them.
     */
    private const HASH_FIELD_ORDER = [
        'req_time', 'merchant_id', 'tran_id', 'amount', 'items', 'shipping',
        'firstname', 'lastname', 'email', 'phone', 'type', 'payment_option',
        'return_url', 'cancel_url', 'continue_success_url', 'return_deeplink',
        'currency', 'custom_fields', 'return_params',
    ];

    public function configured(): bool
    {
        return filled(config('payway.merchant_id')) && filled(config('payway.api_key'));
    }

    public function purchaseUrl(): string
    {
        return rtrim(config('payway.base_url'), '/') . '/api/payment-gateway/v1/payments/purchase';
    }

    /**
     * Calls PayWay's purchase API server-side and returns the decoded JSON —
     * that response (qrString/qrImage/abapay_deeplink, or a rejection like
     * "Wrong Hash") is meant to be read and acted on, not handed to the
     * browser to land on directly.
     */
    public function purchase(array $fields): array
    {
        return Http::asForm()->post($this->purchaseUrl(), $fields)->json() ?? [];
    }

    /** A transaction id PayWay will echo back on its callback, so we know which subscription it's for. */
    public function generateTranId(): string
    {
        return 'khw-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
    }

    /**
     * Builds the full set of hidden fields for the auto-submitting purchase
     * form, including the hash. payment_option is fixed to 'bakong' — this
     * app only offers KHQR checkout, not cards/WeChat/Alipay.
     */
    public function purchaseFields(string $tranId, float $amount, string $planName, User $user, string $currency = 'USD'): array
    {
        [$firstName, $lastName] = $this->splitName($user->displayName());

        $fields = [
            'req_time' => now()->format('YmdHis'),
            'merchant_id' => (string) config('payway.merchant_id'),
            'tran_id' => $tranId,
            'amount' => number_format($amount, 2, '.', ''),
            'items' => base64_encode(json_encode([
                ['name' => $planName, 'quantity' => 1, 'price' => number_format($amount, 2, '.', '')],
            ])),
            'shipping' => '0.00',
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => (string) $user->email,
            'phone' => (string) $user->phone,
            'type' => 'purchase',
            'payment_option' => 'bakong',
            'return_url' => route('account-billing.payway.callback'),
            'cancel_url' => route('account-billing'),
            'continue_success_url' => route('account-billing.payway.return'),
            'return_deeplink' => '',
            'currency' => $currency,
            'custom_fields' => '',
            'return_params' => $tranId,
        ];

        $fields['hash'] = $this->hash($fields);

        return $fields;
    }

    /**
     * Best-effort check of PayWay's callback signature. UNVERIFIED: the
     * field order/composition below (tran_id + apv + status + return_params)
     * is a guess based on the fields PayWay's quickstart page shows in a
     * pushback payload, not a documented formula — confirm against the real
     * API guide before trusting this to gate anything. Fails closed (returns
     * false) whenever it can't be sure, which is what
     * BillingController::paywayCallback() relies on to avoid activating a
     * subscription on an unverified request.
     */
    public function verifyCallback(array $payload, ?string $signatureHeader): bool
    {
        if (! $signatureHeader || ! $this->configured()) {
            return false;
        }

        $concatenated = ($payload['tran_id'] ?? '')
            . ($payload['apv'] ?? '')
            . ($payload['status'] ?? '')
            . ($payload['return_params'] ?? '');

        $expected = base64_encode(hash_hmac('sha512', $concatenated, (string) config('payway.api_key'), true));

        return hash_equals($expected, $signatureHeader);
    }

    private function hash(array $fields): string
    {
        $concatenated = collect(self::HASH_FIELD_ORDER)
            ->map(fn (string $key) => (string) ($fields[$key] ?? ''))
            ->implode('');

        return base64_encode(hash_hmac('sha512', $concatenated, (string) config('payway.api_key'), true));
    }

    private function splitName(string $displayName): array
    {
        $parts = preg_split('/\s+/', trim($displayName), 2, PREG_SPLIT_NO_EMPTY) ?: ['Customer'];

        return [$parts[0], $parts[1] ?? ''];
    }
}
