<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The platform's roles — who holds each one, how the app enforces it, and
 * the part of that which is actually assignable: job post and resume
 * permissions (create/edit/delete/download each), checked live in
 * JobPostController and ResumeController. Everything else is fixed in code
 * (route middleware, User::isAdmin()/isEmployer()/isEmployee()) and shown
 * here read-only.
 */
class RoleController extends Controller
{
    private const JOB_PERMISSION_CODES = [
        Permission::JOB_CREATE,
        Permission::JOB_EDIT,
        Permission::JOB_DELETE,
        Permission::JOB_DOWNLOAD,
    ];

    private const RESUME_PERMISSION_CODES = [
        Permission::RESUME_CREATE,
        Permission::RESUME_EDIT,
        Permission::RESUME_DELETE,
        Permission::RESUME_DOWNLOAD,
    ];

    private const ENFORCEMENT = [
        Role::ADMIN => "The 'admin' middleware and User::isAdmin() checks throughout the app. Full access to every back-office area, regardless of the permissions below.",
        Role::EMPLOYER => "The 'role:employer' route middleware, narrowed further by the job post and resume permissions below (plus per-record ownership checks for job posts).",
        Role::EMPLOYEE => 'No admin access — job-posts and resumes are both behind role:employer, so the permissions below have no route to apply to yet.',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->orderBy('sort_order')
            ->get()
            ->each(fn (Role $role) => $role->enforcement = self::ENFORCEMENT[$role->code] ?? 'Not enforced anywhere in the app yet.');

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissionGroups' => [
                ['label' => 'Job post', 'permissions' => $this->permissionsFor(self::JOB_PERMISSION_CODES)],
                ['label' => 'Resume', 'permissions' => $this->permissionsFor(self::RESUME_PERMISSION_CODES)],
            ],
        ]);
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        foreach (Role::where('code', '!=', Role::ADMIN)->get() as $role) {
            $checked = collect($request->input('permissions.' . $role->id, []))->map(fn ($id) => (int) $id);

            $this->syncScoped($role, $checked);
        }

        return redirect()->route('roles')->withSuccess('Permissions were updated.');
    }

    /**
     * Only touches the assignable slice of $role's permissions — sync()
     * replaces a role's whole permission set, so anything outside job.* and
     * resume.* (a future module's permissions) has to be re-added rather
     * than left to be wiped out.
     */
    private function syncScoped(Role $role, Collection $desiredPermissionIds): void
    {
        $assignableIds = $this->permissionsFor([...self::JOB_PERMISSION_CODES, ...self::RESUME_PERMISSION_CODES])->pluck('id');

        $keepOthers = $role->permissions()->pluck('permissions.id')->diff($assignableIds);

        $role->permissions()->sync($desiredPermissionIds->intersect($assignableIds)->merge($keepOthers)->unique());
    }

    private function permissionsFor(array $codes)
    {
        return Permission::whereIn('code', $codes)
            ->get()
            ->sortBy(fn (Permission $permission) => array_search($permission->code, $codes, true))
            ->values();
    }
}
