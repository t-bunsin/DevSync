<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Module 01 — Identity and Access (5 of 10)
|
| Social login. One row per provider account linked to a ZIN-WORKS user.
| The auth_provider enum is inlined on each column that uses it; MySQL has no
| standalone CREATE TYPE, so the value list is repeated here and in
| login_attempts. Adding a provider means altering both.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('provider', ['password', 'google', 'facebook', 'telegram', 'apple']);
            $table->string('provider_user_id', 255);
            $table->string('provider_email')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['provider', 'provider_user_id']);
            $table->index('user_id', 'idx_identities_user');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_identities');
    }
};
