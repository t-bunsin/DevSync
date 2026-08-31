<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Who posted a job
|
| Records the signed-in user who created a post, so the back office list can
| say who registered it rather than only which employer it is for.
|
| Nullable, and null on delete: posts that predate this column have no author
| to name, and removing a staff account must not take their adverts with it.
| The list renders null as "Unknown".
|
| `uuid`, not `foreignId`: module 01 replaced the legacy bigint users table
| with CHAR(36) UUID keys — see create_identity_users_table.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('company_id');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
