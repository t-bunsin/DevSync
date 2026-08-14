<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('profile');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'display_name' => 'required|string|max:160',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed|required_with:current_password',
        ]);

        $user->display_name = $validated['display_name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?: null;

        if (! empty($validated['new_password'])) {
            if (! Hash::check($validated['current_password'], $user->getAuthPassword())) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['current_password' => 'That is not your current password.']);
            }

            // Assigned through the model's hashed cast. The previous version wrote
            // the raw input straight to the column, storing it unhashed.
            $user->password_hash = $validated['new_password'];
        }

        $user->save();

        return redirect()->route('profile')->withSuccess('Profile updated successfully.');
    }
}
