<?php

namespace App\Http\Controllers;

use App\Models\EmployerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Eager loaded so the list does not fire a roles query per row.
        $users = User::with('roles')->orderBy('created_at')->get();

        // This screen carries the workspace overview since the dashboard was
        // merged into it; the admin shell reads $widget for the sidebar count.
        $widget = ['users' => $users->count()];

        return view('users.index', compact('users', 'widget'));
    }

    public function create()
    {
        return view('users.create', [
            'roles' => Role::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $user = new User();
        $user->setName($validated['first_name'], $validated['last_name']);
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?: null;
        $user->status = $validated['status'];
        $user->preferred_locale = $validated['preferred_locale'];
        $user->password_hash = $validated['password'];   // hashed by the model cast
        $user->save();

        $user->syncRoles([$validated['role']], $validated['role']);
        $this->syncEmployerProfile($user, $validated);

        return redirect()->route('users')->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $user->load('roles', 'employerProfile');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('roles', 'employerProfile');

        return view('users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate($this->rules($user));

        $user->setName($validated['first_name'], $validated['last_name']);
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?: null;
        $user->status = $validated['status'];
        $user->preferred_locale = $validated['preferred_locale'];

        // Leave the current password untouched unless a new one was supplied.
        if (! empty($validated['password'])) {
            $user->password_hash = $validated['password'];
        }

        $user->save();

        $user->syncRoles([$validated['role']], $validated['role']);
        $this->syncEmployerProfile($user, $validated);

        return redirect()->route('users')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('users')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users')->with('success', 'User deleted successfully!');
    }

    /**
     * @param  User|null  $user  the record being updated, excluded from unique checks
     */
    private function rules(?User $user = null): array
    {
        return [
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:80',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user?->id)],
            'role' => ['required', Rule::exists('roles', 'code')],
            'status' => ['required', Rule::in([
                User::STATUS_PENDING,
                User::STATUS_ACTIVE,
                User::STATUS_SUSPENDED,
                User::STATUS_BANNED,
            ])],
            'preferred_locale' => ['required', Rule::in(['en', 'km'])],
            'company_name' => ['nullable', 'required_if:role,' . Role::EMPLOYER, 'string', 'max:255'],
            // Required on create, optional on update where blank means "keep current".
            'password' => [$user ? 'nullable' : 'required', 'string', 'confirmed', 'min:8'],
        ];
    }

    /**
     * company_name lives outside module 01's thin users table, so it is written
     * alongside the account and cleared when the user is no longer an employer.
     */
    private function syncEmployerProfile(User $user, array $validated): void
    {
        if ($validated['role'] !== Role::EMPLOYER) {
            $user->employerProfile()->delete();

            return;
        }

        EmployerProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['company_name' => $validated['company_name']]
        );
    }
}
