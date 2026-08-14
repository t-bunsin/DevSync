<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Module 01 — Identity and Access (7 of 10)
|
| Verification and reset tokens. Only the hash is stored.
|
| The source index is (user_id, purpose) WHERE consumed_at IS NULL; consumed_at
| is folded into the key here so unconsumed tokens are still found from the
| index alone.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('purpose', ['email_verify', 'password_reset', 'phone_otp', 'invite']);
            $table->string('token_hash', 128);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'purpose', 'consumed_at'], 'idx_tokens_lookup');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
    }
};
