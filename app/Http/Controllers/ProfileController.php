<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => ['nullable', 'confirmed', 'required_with:current_password',
                \App\Http\Controllers\Auth\RegisterController::passwordRules()],
            // A real image, one of three web formats, big enough to be worth
            // showing and small enough that a phone snapshot still uploads.
            'photo' => User::PHOTO_RULES,
        ], [
            'photo.image' => 'The photo must be an image file.',
            'photo.mimes' => 'The photo must be a JPG, PNG or WebP file.',
            'photo.max' => 'The photo must be 2 MB or smaller.',
            'photo.dimensions' => 'The photo must be at least 100 x 100 pixels.',
        ]);

        $user->setName($validated['first_name'], $validated['last_name']);
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

        $this->syncAvatar($user, $request);

        $user->save();

        return redirect()->route('profile')->withSuccess('Profile updated successfully.');
    }

    /**
     * Applies a new upload, or clears the current photo when the remove box is
     * ticked. Replacing always deletes the old file first, so swapping a photo
     * repeatedly does not leave orphans behind on disk. Mirrors
     * ResumeController::syncPhoto().
     */
    private function syncAvatar(User $user, Request $request): void
    {
        if ($request->hasFile('photo')) {
            $this->deleteAvatar($user);
            $user->avatar_url = $request->file('photo')->store('avatars', 'public');

            return;
        }

        if ($request->boolean('remove_photo')) {
            $this->deleteAvatar($user);
            $user->avatar_url = null;
        }
    }

    /**
     * Only ever deletes a file this app stored. An avatar_url holding an
     * external URL has nothing on disk to remove.
     */
    private function deleteAvatar(User $user): void
    {
        if ($user->hasUploadedAvatar()) {
            Storage::disk('public')->delete($user->avatar_url);
        }
    }
}
