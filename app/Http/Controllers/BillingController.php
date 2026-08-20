<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\PayWayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Account billing: choose a package (config/plans.php), confirm, then pay
 * through ABA PayWay's Bakong/KHQR checkout. See PayWayService for the
 * caveats on the hash formula this was built against — it hasn't been
 * verified against PayWay's official docs or a live sandbox call.
 */
class BillingController extends Controller
{
    public function __construct(private readonly PayWayService $payWay)
    {
        $this->middleware('auth')->except(['paywayCallback']);
    }

    public function index(): View
    {
        return view('account-billing.index', [
            'tiers' => config('plans.tiers'),
            'subscription' => Auth::user()->subscription,
        ]);
    }

    public function checkout(Request $request): View
    {
        $validated = $request->validate([
            'plan_id' => ['required', Rule::in($this->planIds())],
            'billing_period' => ['required', 'in:monthly,annual'],
        ]);

        $plan = $this->findPlan($validated['plan_id']);

        return view('account-billing.checkout', [
            'plan' => $plan,
            'billingPeriod' => $validated['billing_period'],
            'amount' => $this->amountFor($plan, $validated['billing_period']),
            'payWayConfigured' => $this->payWay->configured(),
        ]);
    }

    /**
     * Confirms the order. A free plan has nothing to charge, so it activates
     * immediately; anything else calls PayWay's purchase API server-side and,
     * once PayWay accepts it, shows the returned KHQR code — the pay view
     * then polls paywayStatus() until the callback below confirms or rejects
     * the payment. PayWay decides whether the payment actually succeeded,
     * not this app.
     */
    public function pay(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', Rule::in($this->planIds())],
            'billing_period' => ['required', 'in:monthly,annual'],
        ]);

        $plan = $this->findPlan($validated['plan_id']);
        $amount = $this->amountFor($plan, $validated['billing_period']);
        $user = Auth::user();

        if ($amount <= 0) {
            Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tran_id' => null,
                    'plan_id' => $plan['id'],
                    'billing_period' => $validated['billing_period'],
                    'payment_option' => null,
                    'amount' => 0,
                    'status' => Subscription::STATUS_ACTIVE,
                    'started_at' => now(),
                ]
            );

            return redirect()->route('account-billing')
                ->withSuccess("You're now on the {$plan['name']} plan.");
        }

        if (! $this->payWay->configured()) {
            return redirect()->route('account-billing.checkout', $validated)
                ->withErrors(['payment' => 'Payment is not configured yet. Set PAYWAY_MERCHANT_ID and PAYWAY_API_KEY in .env.']);
        }

        $tranId = $this->payWay->generateTranId();
        $fields = $this->payWay->purchaseFields($tranId, $amount, $plan['name'], $user);
        $response = $this->payWay->purchase($fields);

        // "00" is PayWay's success code; anything else (wrong hash, invalid
        // field, ...) means no session was actually opened on their end, so
        // nothing is recorded here — a pending row with a tran_id PayWay
        // never issued would just be a dead end for the callback to find.
        if (($response['status']['code'] ?? null) !== '00') {
            Log::warning('PayWay purchase request rejected', ['tran_id' => $tranId, 'response' => $response]);

            return redirect()->route('account-billing.checkout', $validated)
                ->withErrors(['payment' => $response['status']['message'] ?? 'PayWay rejected the payment request. Please try again.']);
        }

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tran_id' => $tranId,
                'plan_id' => $plan['id'],
                'billing_period' => $validated['billing_period'],
                'payment_option' => 'bakong',
                'amount' => $amount,
                'status' => Subscription::STATUS_PENDING,
                'started_at' => now(),
            ]
        );

        return view('account-billing.pay', [
            'tranId' => $tranId,
            'qrImage' => $response['qrImage'] ?? null,
            'deeplink' => $response['abapay_deeplink'] ?? null,
            'appStore' => $response['app_store'] ?? null,
            'playStore' => $response['play_store'] ?? null,
            // PayWay's response carries no expiry of its own, so this is a
            // fixed window we impose client-side — not something read back
            // from PayWay. Measured from now (request time), not page load,
            // so a slow render or a refresh still reflects real time left.
            'expiresAt' => now()->addMinutes(5)->timestamp,
        ]);
    }

    /** Polled by the QR page so it can move on once the callback below confirms or rejects the payment. */
    public function paywayStatus(Request $request): JsonResponse
    {
        $validated = $request->validate(['tran_id' => ['required', 'string']]);

        $subscription = Subscription::where('tran_id', $validated['tran_id'])
            ->where('user_id', Auth::id())
            ->first();

        return response()->json(['status' => $subscription?->status ?? 'not_found']);
    }

    /**
     * PayWay calls this server-to-server once payment completes. Whether to
     * trust it hinges on a signature this app cannot fully verify yet (see
     * PayWayService) — so it only activates the subscription when the
     * recomputed hash matches what PayWay sent; otherwise it logs the
     * payload for manual reconciliation and leaves the subscription pending.
     */
    public function paywayCallback(Request $request): Response
    {
        $payload = $request->all();
        $tranId = $payload['tran_id'] ?? $payload['return_params'] ?? null;

        Log::info('PayWay callback received', ['tran_id' => $tranId, 'payload' => $payload]);

        $subscription = $tranId ? Subscription::where('tran_id', $tranId)->first() : null;

        if (! $subscription) {
            Log::warning('PayWay callback: no matching subscription', ['tran_id' => $tranId]);

            return response('OK', 200);
        }

        $signatureHeader = $request->header('X-PAYWAY-HMAC-SHA512') ?? $request->header('X_PAYWAY_HMAC_SHA512');

        if (! $this->payWay->verifyCallback($payload, $signatureHeader)) {
            Log::warning('PayWay callback: signature not verified, leaving subscription pending', ['tran_id' => $tranId]);

            return response('OK', 200);
        }

        $succeeded = ($payload['status'] ?? null) === '0';

        $subscription->update([
            'status' => $succeeded ? Subscription::STATUS_ACTIVE : Subscription::STATUS_FAILED,
            'started_at' => $succeeded ? now() : $subscription->started_at,
        ]);

        return response('OK', 200);
    }

    /** Where PayWay sends the user's browser back to after paying. The real state lives in the callback above, so this just points them at their billing page. */
    public function paywayReturn(): RedirectResponse
    {
        $subscription = Auth::user()->subscription;

        return redirect()->route('account-billing')->withSuccess(
            match ($subscription?->status) {
                Subscription::STATUS_ACTIVE => "You're now on the {$subscription->plan()['name']} plan.",
                Subscription::STATUS_FAILED => 'That payment did not go through. Please try again.',
                default => "We're still confirming your payment — refresh in a moment if this doesn't update.",
            }
        );
    }

    private function planIds(): array
    {
        return collect(config('plans.tiers'))->pluck('id')->all();
    }

    private function findPlan(string $planId): array
    {
        return collect(config('plans.tiers'))->firstWhere('id', $planId);
    }

    /** Recomputed server-side, same formula as the public pricing page, so a tampered client value can't set the price. */
    private function amountFor(array $plan, string $billingPeriod): float
    {
        $monthly = $plan['monthly'];

        if ($billingPeriod === 'monthly') {
            return (float) $monthly;
        }

        return round($monthly * (1 - config('plans.annual_discount')), 2);
    }
}
