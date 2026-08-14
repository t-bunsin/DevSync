<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Module 01 — Identity and Access (6 of 10)
|
| One row per active device. Distinct from Laravel's own `sessions` table
| (the session-driver store) — that one stays as it is.
|
| The partial index `WHERE revoked_at IS NULL` becomes a composite
| (user_id, revoked_at); MySQL cannot filter an index, but the predicate is
| still resolved from it rather than from the row.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->unsignedSmallInteger('active_role_id')->nullable();   // which hat they are wearing
            $table->string('token_hash', 128)->unique();                  // store the hash, never the token
            $table->text('user_agent')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();

            $table->index(['user_id', 'revoked_at'], 'idx_sessions_active');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('active_role_id')->references('id')->on('roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
    }
};
