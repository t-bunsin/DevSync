<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| ZIN-WORKS | View permissions, and the applicant inbox
|
| Three gaps the first two permission migrations left:
|
|  - job.view / resume.view — reading a list was never a grant of its own.
|    Job posts were readable by any employer, and resumes by anyone holding
|    any one resume.* code, which made "download only" impossible to express.
|  - application.view / application.download — the applicant inbox and the CV
|    download behind it had no permission at all, only role:employer plus the
|    per-post ownership check (which stays).
|
| Grants reproduce today's access rather than widening it: employer gets the
| job and application codes, because an employer can already read job posts,
| open the inbox for their own post and download those CVs. Employer does NOT
| get resume.view — resumes are admin-granted per the resume migration, and
| handing out browse access here would be a new permission, not a migration.
|
| Admin gets everything, and must keep getting everything in every future
| permission migration: User::hasPermission() no longer short-circuits on
| isAdmin(), so an admin holds exactly what this table says they hold.
*/
return new class extends Migration
{
    private const PERMISSIONS = [
        ['code' => 'job.view', 'description' => 'Browse job posts and open one.'],
        ['code' => 'resume.view', 'description' => 'Browse resumes and open one.'],
        ['code' => 'application.view', 'description' => 'Open the applicant inbox for a job post.'],
        ['code' => 'application.download', 'description' => "Download an applicant's CV."],
    ];

    private const GRANTS = [
        'admin' => ['job.view', 'resume.view', 'application.view', 'application.download'],
        'employer' => ['job.view', 'application.view', 'application.download'],
    ];

    public function up(): void
    {
        DB::table('permissions')->insert(self::PERMISSIONS);

        $ids = DB::table('permissions')
            ->whereIn('code', array_column(self::PERMISSIONS, 'code'))
            ->pluck('id', 'code');

        foreach (self::GRANTS as $roleCode => $codes) {
            $roleId = DB::table('roles')->where('code', $roleCode)->value('id');

            if (! $roleId) {
                continue;
            }

            DB::table('role_permissions')->insert(
                collect($codes)->map(fn (string $code) => [
                    'role_id' => $roleId,
                    'permission_id' => $ids[$code],
                ])->all()
            );
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('code', array_column(self::PERMISSIONS, 'code'))
            ->pluck('id');

        DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
