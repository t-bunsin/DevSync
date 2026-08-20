<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\PayWayService;
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
     * immediately; anything else creates a 'pending' subscription and hands
     * the browser an auto-submitting form to PayWay's hosted checkout —
     * PayWay decides whether the payment actually succeeded, not this app.
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
            'purchaseUrl' => $this->payWay->purchaseUrl(),
            'fields' => $this->payWay->purchaseFields($tranId, $amount, $plan['name'], $user),
        ]);
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
