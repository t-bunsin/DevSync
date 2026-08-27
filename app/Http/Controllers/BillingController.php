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
 * through ABA PayWay's Bakong/KHQR checkout. The purchase leg is confirmed
 * working against PayWay's sandbox; the callback signature that gates
 * activation is not yet verified — see PayWayService for both.
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

    /**
     * The billing register: every account's subscription in one table.
     *
     * Admin-only at the route, because this is the whole table rather than
     * the caller's own row — subscriptions.user_id is unique, so a per-user
     * "list" would only ever be the single row index() already shows.
     */
    public function list(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, Subscription::STATUSES, true) ? $status : null;
        $search = trim((string) $request->query('q'));

        $subscriptions = Subscription::query()
            // Eager loaded: the table prints a name and email on every row.
            ->with('user')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($group) use ($search) {
                $group->where('tran_id', 'like', "%{$search}%")
                    ->orWhere('plan_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%"));
            }))
            ->orderByDesc('created_at')
            ->get();

        // Counted off the whole table, not the filtered set, so the tiles do
        // not change meaning when a filter is applied — same call shape as
        // ComplianceController::index().
        $counts = Subscription::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('account-billing.list', [
            'subscriptions' => $subscriptions,
            'counts' => $counts,
            // Only active plans are money actually being collected; pending and
            // failed rows would overstate it.
            'activeValue' => Subscription::query()
                ->where('status', Subscription::STATUS_ACTIVE)
                ->sum('amount'),
            'activeStatus' => $status,
            'searchTerm' => $search,
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
            'pricing' => $this->pricing($plan, $validated['billing_period']),
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
        // Same figure the checkout page showed as "Total due today".
        $amount = $this->pricing($plan, $validated['billing_period'])['due_today'];
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
            // Shown on the KHQR dialog so the payer can check the amount
            // against what their banking app is about to charge them.
            'amount' => $amount,
            'planName' => $plan['name'],
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

    /**
     * The one place a price is decided. Both the figures the checkout page
     * prints and the amount handed to PayWay come from here, so the customer
     * cannot be shown one total and charged another — which is exactly what
     * happened while the view multiplied by 12 for annual and pay() charged
     * a single month.
     *
     * 'per_month' is the headline rate (what the plan grid advertises);
     * 'due_today' is what actually gets charged — twelve of those months up
     * front on annual, one month on monthly.
     */
    private function pricing(array $plan, string $billingPeriod): array
    {
        $perMonth = $this->amountFor($plan, $billingPeriod);
        $months = $billingPeriod === 'annual' ? 12 : 1;

        return [
            'per_month' => $perMonth,
            'months' => $months,
            'due_today' => round($perMonth * $months, 2),
            // What the same term would have cost without the annual discount,
            // so the page can show the saving rather than assert it.
            'undiscounted' => round(((float) $plan['monthly']) * $months, 2),
        ];
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
