<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| ZIN-WORKS | Resume permissions
|
| Same idiom as job.* (see seed_job_permissions): four actions, assignable
| per role from the User Role & Permission page.
|
| Unlike job posts, resumes had NO non-admin route at all before this — the
| whole resource sat behind the 'admin' route middleware (see ResumeController).
| So "seeded to match today's behaviour" here means admin gets all four and
| employer/employee get none: granting access is an explicit admin decision
| going forward, not a change that happens on migrate.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->insert([
            ['code' => 'resume.create', 'description' => 'Register a new resume.'],
            ['code' => 'resume.edit', 'description' => 'Change an existing resume.'],
            ['code' => 'resume.delete', 'description' => 'Remove a resume.'],
            ['code' => 'resume.download', 'description' => 'Download a resume as a PDF.'],
        ]);

        $permissionIds = DB::table('permissions')
            ->whereIn('code', ['resume.create', 'resume.edit', 'resume.delete', 'resume.download'])
            ->pluck('id');

        $adminId = DB::table('roles')->where('code', 'admin')->value('id');

        if ($adminId) {
            DB::table('role_permissions')->insert(
                $permissionIds->map(fn ($permissionId) => [
                    'role_id' => $adminId,
                    'permission_id' => $permissionId,
                ])->all()
            );
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('code', ['resume.create', 'resume.edit', 'resume.delete', 'resume.download'])
            ->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
