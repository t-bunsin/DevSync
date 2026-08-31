<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Give the existing job posts an author
|
| add_created_by_to_job_posts_table added the column as nullable, so every post
| that already existed — and every post the seeder wrote before it learned to
| attribute them — carries NULL and the back office list renders it as
| "Unknown". This fills those in.
|
| The oldest administrator is the stand-in: on a real install that is the
| account the site was set up with, and it is the only honest answer available
| once the fact of who wrote the post was never recorded. Any post that already
| names an author is left exactly as it is.
|
| Nothing happens when there is no account to point at (a fresh database being
| migrated before it is seeded); the seeder attributes its own rows, so those
| never arrive here as NULL in the first place.
*/
return new class extends Migration
{
    public function up(): void
    {
        // Guarded: this runs on databases where the identity module may not
        // have been applied, and a missing table would abort the migration.
        if (! Schema::hasTable('users') || ! Schema::hasTable('job_posts')) {
            return;
        }

        $author = $this->oldestAdminId() ?? $this->oldestUserId();

        if ($author === null) {
            return;
        }

        DB::table('job_posts')->whereNull('created_by')->update(['created_by' => $author]);
    }

    /**
     * Deliberately not reversed: `created_by` was NULL before this ran, and
     * blanking every author on the way down would also discard the ones that
     * were set legitimately since. Rolling the column away is the job of the
     * migration that added it.
     */
    public function down(): void
    {
        //
    }

    private function oldestAdminId(): ?string
    {
        if (! Schema::hasTable('user_roles') || ! Schema::hasTable('roles')) {
            return null;
        }

        return DB::table('users')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.code', 'admin')
            ->whereNull('users.deleted_at')
            ->orderBy('users.created_at')
            ->value('users.id');
    }

    private function oldestUserId(): ?string
    {
        return DB::table('users')->whereNull('deleted_at')->orderBy('created_at')->value('id');
    }
};
