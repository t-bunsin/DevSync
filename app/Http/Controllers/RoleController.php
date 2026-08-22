<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The platform's roles — who holds each one, how the app enforces it, and
 * the part of that which is actually assignable: the job post, resume and
 * applicant-inbox modules, checked live in JobPostController,
 * ResumeController and JobApplicationController. Every role is assignable,
 * admin included — User::hasPermission() no longer waves admins through —
 * but which *areas* a role may enter is still fixed in code (route
 * middleware, User::isAdmin()/isEmployer()/isEmployee()), which is why a job
 * seeker's grants sit dormant until a route admits the role.
 */
class RoleController extends Controller
{
    private const JOB_PERMISSION_CODES = [
        Permission::JOB_VIEW,
        Permission::JOB_CREATE,
        Permission::JOB_EDIT,
        Permission::JOB_DELETE,
        Permission::JOB_DOWNLOAD,
    ];

    private const RESUME_PERMISSION_CODES = [
        Permission::RESUME_VIEW,
        Permission::RESUME_CREATE,
        Permission::RESUME_EDIT,
        Permission::RESUME_DELETE,
        Permission::RESUME_DOWNLOAD,
    ];

    private const APPLICATION_PERMISSION_CODES = [
        Permission::APPLICATION_VIEW,
        Permission::APPLICATION_DOWNLOAD,
    ];

    /**
     * The per-role warning above the switches. Every role is assignable now,
     * so these say what a grant actually does rather than why it is disabled:
     * admin's bites immediately, job seeker's is stored and waits for a route.
     */
    private const NOTICES = [
        Role::ADMIN => [
            'tone' => 'caution',
            'text' => 'Switching a function off here takes it away from every administrator, including you. This page is gated by isAdmin() rather than by these switches, so you can always come back and switch it on again.',
        ],
        Role::EMPLOYEE => [
            'tone' => 'info',
            'text' => 'Grants are saved, but job posts, resumes and the applicant inbox all sit behind the role:employer route middleware, which turns a job seeker away before these switches are read. Set them up now if you like — they start applying the moment a route admits this role.',
        ],
    ];

    private const ENFORCEMENT = [
        Role::ADMIN => "The 'admin' middleware and User::isAdmin() checks throughout the app. Every back-office area stays reachable, but what an admin may do inside job posts, resumes and the applicant inbox is now these switches.",
        Role::EMPLOYER => "The 'role:employer' route middleware, narrowed further by the switches below (plus per-record ownership checks for job posts and the applicant inbox).",
        Role::EMPLOYEE => 'No admin access yet — every module below sits behind role:employer, so a job seeker is stopped at the route before these switches are consulted.',
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
            ->each(function (Role $role) {
                $role->enforcement = self::ENFORCEMENT[$role->code] ?? 'Not enforced anywhere in the app yet.';
                $role->notice = self::NOTICES[$role->code]['text'] ?? null;
                $role->notice_tone = self::NOTICES[$role->code]['tone'] ?? 'info';
            });

        return view('admin.roles.index', [
            'roles' => $roles,
            'permissionGroups' => [
                [
                    'key' => 'job',
                    'label' => 'Job post',
                    'icon' => 'briefcase',
                    'blurb' => 'Checked live by JobPostController, on top of per-record ownership.',
                    'permissions' => $this->permissionsFor(self::JOB_PERMISSION_CODES),
                ],
                [
                    'key' => 'resume',
                    'label' => 'Resume',
                    'icon' => 'file-text',
                    'blurb' => 'Checked live by ResumeController for every resume action.',
                    'permissions' => $this->permissionsFor(self::RESUME_PERMISSION_CODES),
                ],
                [
                    'key' => 'application',
                    'label' => 'Job applications',
                    'icon' => 'inbox',
                    'blurb' => 'The applicant inbox, on top of the ownership check for the job post.',
                    'permissions' => $this->permissionsFor(self::APPLICATION_PERMISSION_CODES),
                ],
            ],
        ]);
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        foreach (Role::all() as $role) {
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
        $assignableIds = $this->permissionsFor([
            ...self::JOB_PERMISSION_CODES,
            ...self::RESUME_PERMISSION_CODES,
            ...self::APPLICATION_PERMISSION_CODES,
        ])->pluck('id');

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
