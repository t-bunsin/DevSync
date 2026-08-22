<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| KH-WORKS | Job post permissions
|
| The permissions/role_permissions tables were created back in
| create_permissions_tables and deliberately left unseeded — "permission
| codes belong to the modules that enforce them". This is that first module:
| the four actions JobPostController now checks via User::hasPermission().
|
| Seeded to match today's behaviour exactly, so turning this on changes
| nothing until an admin edits the matrix on the Roles page: employer
| already has full run of their own job posts (authorizeOwner()) and the
| export route, so employer starts with all four; employee has no
| job-posts routes at all (blocked by the 'role:employer' middleware before
| a permission check would even run), so employee starts with none. Admin
| is seeded with all four for a consistent directory row, but
| User::hasPermission() short-circuits true for admin regardless — that row
| in the matrix is display-only.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->insert([
            ['code' => 'job.create', 'description' => 'Add a new job post.'],
            ['code' => 'job.edit', 'description' => 'Change an existing job post.'],
            ['code' => 'job.delete', 'description' => 'Remove a job post.'],
            ['code' => 'job.download', 'description' => 'Export job posts to a spreadsheet.'],
        ]);

        $permissionIds = DB::table('permissions')
            ->whereIn('code', ['job.create', 'job.edit', 'job.delete', 'job.download'])
            ->pluck('id');

        foreach (['admin', 'employer'] as $roleCode) {
            $roleId = DB::table('roles')->where('code', $roleCode)->value('id');

            if (! $roleId) {
                continue;
            }

            DB::table('role_permissions')->insert(
                $permissionIds->map(fn ($permissionId) => [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ])->all()
            );
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('code', ['job.create', 'job.edit', 'job.delete', 'job.download'])
            ->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
