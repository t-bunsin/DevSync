<?php

namespace App\Http\Controllers;

use App\Models\EmployerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $viewer = $request->user();

        $from = $this->dateInput($request->query('from'));
        $to = $this->dateInput($request->query('to'));

        // A backwards range would silently return nothing, so read it as given.
        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        $activeRole = $this->assignableRoleCodes($viewer)->contains($request->query('role'))
            ? $request->query('role')
            : null;

        // Eager loaded so the list does not fire a roles query per row.
        $users = User::query()
            ->with('roles')
            ->visibleTo($viewer)
            ->withRole($activeRole)
            ->search($request->query('q'))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->orderBy('created_at')
            ->get();

        // Counted off everything this viewer may see rather than the filtered
        // set, so the tiles keep their meaning under a filter.
        $counts = Role::query()
            ->withCount(['users' => fn ($query) => $query->visibleTo($viewer)])
            ->orderBy('sort_order')
            ->get()
            ->pluck('users_count', 'code');

        $total = User::query()->visibleTo($viewer)->count();

        // This screen carries the workspace overview since the dashboard was
        // merged into it; the admin shell reads $widget for the sidebar count.
        $widget = ['users' => $total];

        return view('users.index', [
            'users' => $users,
            'counts' => $counts,
            'total' => $total,
            'roles' => $this->assignableRoles($viewer),
            'activeRole' => $activeRole,
            'searchTerm' => $request->query('q'),
            'fromDate' => $from,
            'toDate' => $to,
            'widget' => $widget,
        ]);
    }

    public function create(Request $request)
    {
        return view('users.create', [
            'roles' => $this->assignableRoles($request->user()),
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

    public function show(Request $request, User $user)
    {
        $user->load('roles', 'employerProfile');
        $this->assertVisible($user, $request->user());

        return view('users.show', compact('user'));
    }

    public function edit(Request $request, User $user)
    {
        $user->load('roles', 'employerProfile');
        $this->assertVisible($user, $request->user());

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles($request->user()),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertVisible($user, $request->user());

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

    public function destroy(Request $request, User $user)
    {
        $this->assertVisible($user, $request->user());

        if (Auth::id() === $user->id) {
            return redirect()->route('users')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users')->with('success', 'User deleted successfully!');
    }

    /**
     * An admin account is not just hidden from the list for everyone else — it
     * is unreachable. 404 rather than 403 so the response does not confirm the
     * account exists.
     */
    private function assertVisible(User $user, ?User $viewer): void
    {
        abort_if($user->isAdmin() && ! $viewer?->isAdmin(), 404);
    }

    /**
     * Roles this viewer may hand out. Only an admin can create or promote
     * another admin; without this, hiding admins from the list would still
     * leave the role selectable on the form.
     */
    private function assignableRoles(?User $viewer): Collection
    {
        return Role::query()
            ->when(! $viewer?->isAdmin(), fn ($query) => $query->where('code', '!=', Role::ADMIN))
            ->orderBy('sort_order')
            ->get();
    }

    private function assignableRoleCodes(?User $viewer): Collection
    {
        return $this->assignableRoles($viewer)->pluck('code');
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
            'role' => ['required', Rule::in($this->assignableRoleCodes(Auth::user())->all())],
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
