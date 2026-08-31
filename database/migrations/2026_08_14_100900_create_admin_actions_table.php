<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Module 01 — Identity and Access (9 of 10)
|
| Who moderated what.
|
| DEVIATION FROM THE SOURCE: admin_id is declared
|     UUID NOT NULL REFERENCES users(id) ON DELETE SET NULL
| which cannot hold. The FK action writes NULL into a NOT NULL column, so
| deleting an admin raises a constraint violation instead of tidying the log —
| the audit trail blocks the delete. Made nullable here so the action survives
| the account, matching login_attempts.user_id. Swap to restrictOnDelete
| instead if an audit row must always name a live admin.
|
| entity_id is CHAR(36) per the source. Jobs and companies in this codebase
| still use BIGINT keys, so no FK can be declared against them and the column
| will not hold their ids until those modules move to UUIDs.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('admin_id')->nullable();
            $table->string('action', 60);         // 'job.approve', 'user.suspend'
            $table->string('entity_type', 40);    // 'job', 'company', 'user'
            $table->uuid('entity_id');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id'], 'idx_admin_actions_entity');

            $table->foreign('admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_actions');
    }
};
