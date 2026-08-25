<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| KH-WORKS | Resume grants for the job seeker role
|
| ResumeController used to let a candidate act on their own resume by
| ownership alone, returning before the permission check — which is why the
| resume.* switches on the Job Seeker row of the User Role & Permission page
| did nothing. The controller now asks for the grant as well as ownership, so
| this migration hands the employee role the codes that ownership was already
| giving them: browse (their own list), create, edit and download.
|
| Same rule as the migrations before it — reproduce today's access, do not
| widen it — so resume.delete is left out. Candidates are refused deletion in
| the controller regardless; the register keeps the row.
|
| An admin switching any of these off now actually takes it away, which is the
| point of the page.
*/
return new class extends Migration
{
    private const CODES = ['resume.view', 'resume.create', 'resume.edit', 'resume.download'];

    public function up(): void
    {
        $roleId = DB::table('roles')->where('code', 'employee')->value('id');

        if (! $roleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('code', self::CODES)
            ->pluck('id');

        // Re-running must not duplicate a grant an admin already made by hand.
        $existing = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->whereIn('permission_id', $permissionIds)
            ->pluck('permission_id');

        $rows = $permissionIds
            ->diff($existing)
            ->map(fn ($permissionId) => [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ])
            ->all();

        if ($rows) {
            DB::table('role_permissions')->insert($rows);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('code', 'employee')->value('id');

        if (! $roleId) {
            return;
        }

        $permissionIds = DB::table('permissions')->whereIn('code', self::CODES)->pluck('id');

        DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }
};
