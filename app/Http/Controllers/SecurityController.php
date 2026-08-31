<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * The signed-in person's own security page: change the password, and read back
 * how the account is protected. It is deliberately self-service only — an admin
 * changing somebody else's password does it from the user form in
 * UserController, which is where the role and status gates live.
 */
class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('account-security.index', [
            'user' => Auth::user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        // A social-only account has no password_hash by design (see the module 01
        // migration), so it is setting a first password rather than replacing one
        // and has nothing to prove ownership of.
        $hasPassword = $user->password_hash !== null;

        $validated = $request->validate([
            'current_password' => [$hasPassword ? 'required' : 'nullable', 'string'],
            // Same policy as sign-up and the admin user form, so a password
            // changed here is never weaker than the one it replaces.
            'password' => ['required', 'confirmed', RegisterController::passwordRules()],
        ]);

        if ($hasPassword && ! Hash::check($validated['current_password'], $user->getAuthPassword())) {
            return back()->withErrors(['current_password' => __('ui.bo.security.wrong_password')]);
        }

        // Assigned through the model's hashed cast, never pre-hashed here.
        $user->password_hash = $validated['password'];
        $user->save();

        return redirect()->route('security')->with('success', __('ui.bo.security.updated'));
    }
}
