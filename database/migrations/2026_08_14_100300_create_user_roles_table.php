<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Module 01 — Identity and Access (3 of 10)
|
| A user may hold more than one role, so a person can be both an employer and
| a job seeker on a single login.
|
| The source enforces "exactly one primary role per user" with a partial unique
| index:  CREATE UNIQUE INDEX ... ON user_roles (user_id) WHERE is_primary
| MySQL has no partial indexes, so it is rebuilt with a stored generated column
| that holds user_id only on the primary row and NULL otherwise. MySQL permits
| repeated NULLs in a UNIQUE index, so only the is_primary rows can collide —
| the same guarantee, enforced by the engine rather than by application code.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->unsignedSmallInteger('role_id');
            $table->boolean('is_primary')->default(false);   // the role the UI opens in
            $table->uuid('granted_by')->nullable();
            $table->timestamp('granted_at')->useCurrent();

            // VIRTUAL, not STORED: a stored generated column forces a COPY rebuild
            // of the table, and Laravel adds the foreign keys below in a separate
            // ALTER that then cannot run (errno 1215). A virtual column is computed
            // on read and still takes the unique secondary index that does the work.
            $table->char('primary_user_id', 36)
                ->virtualAs('CASE WHEN `is_primary` THEN `user_id` END')
                ->nullable();

            $table->primary(['user_id', 'role_id']);
            $table->index('role_id', 'idx_user_roles_role');
            $table->unique('primary_user_id', 'idx_one_primary_role');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->restrictOnDelete();
            $table->foreign('granted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
