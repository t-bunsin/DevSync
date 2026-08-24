<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\VerifyEmailCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The step between "registered" and "signed in": prove the address.
 *
 * Who is verifying comes from the session, never from the request, so the
 * screen can only ever act on the account this browser just registered with.
 * Without that, the code form would be an oracle for guessing against any
 * address someone cared to type.
 */
class EmailVerificationCodeController extends Controller
{
    public const SESSION_KEY = 'verification.user_id';

    public function __construct()
    {
        $this->middleware('guest');
    }

    /** Puts an account into the verification flow and mails it a fresh code. */
    public static function start(Request $request, User $user): void
    {
        $request->session()->put(self::SESSION_KEY, $user->id);

        $user->notify(new VerifyEmailCode(EmailVerificationCode::issueFor($user)));
    }

    public function show(Request $request): View|RedirectResponse
    {
        if (! $user = $this->pendingUser($request)) {
            return redirect()->route('register');
        }

        return view('auth.verify-code', [
            'user' => $user,
            'waitSeconds' => $user->emailVerificationCode?->resendWaitSeconds() ?? 0,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        if (! $user = $this->pendingUser($request)) {
            return redirect()->route('register');
        }

        $request->validate(['code' => ['required', 'digits:6']]);

        $record = $user->emailVerificationCode;

        if (! $record || ! $record->matches($request->input('code'))) {
            return back()->withErrors(['code' => $this->failureReason($record)]);
        }

        $user->forceFill(['email_verified_at' => now()])->save();
        $record->delete();

        $request->session()->forget(self::SESSION_KEY);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended(route($user->homeRouteName()))
            ->withSuccess('Your email is verified. Welcome to KH-WORKS.');
    }

    public function resend(Request $request): RedirectResponse
    {
        if (! $user = $this->pendingUser($request)) {
            return redirect()->route('register');
        }

        // The row's own clock, not the throttle middleware's: the middleware
        // counts requests from an IP, this counts mail sent to one address.
        if ($wait = $user->emailVerificationCode?->resendWaitSeconds()) {
            return back()->withErrors([
                'code' => "Please wait {$wait} more " . str('second')->plural($wait) . ' before asking for another code.',
            ]);
        }

        $user->notify(new VerifyEmailCode(EmailVerificationCode::issueFor($user)));

        return back()->withSuccess('A new code is on its way to ' . $user->email . '.');
    }

    /** The account this browser is verifying, or null if there isn't one. */
    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get(self::SESSION_KEY);

        $user = $id ? User::with('emailVerificationCode')->find($id) : null;

        // Already verified — nothing left to prove, so drop out of the flow.
        return $user && $user->email_verified_at === null ? $user : null;
    }

    /** Says which wall the guess hit, without confirming any digits. */
    private function failureReason(?EmailVerificationCode $record): string
    {
        return match (true) {
            $record === null => 'That code has expired. Ask for a new one.',
            $record->isExpired() => 'That code has expired. Ask for a new one.',
            $record->isSpent() => 'Too many attempts. Ask for a new code.',
            default => 'That code is not right. Check the email and try again.',
        };
    }
}
