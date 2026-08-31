<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Module 01 — Identity and Access (8 of 10)
|
| Rate limiting and abuse investigation. user_id is null when the identifier
| matched no account — that is the common case for credential stuffing, so it
| must stay nullable.
|
| The lookup index is created by hand because Laravel's schema builder cannot
| express a DESC key part. MySQL 8 honours descending indexes (5.7 parsed and
| ignored them), so the recent-attempts scan reads forward off the index.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id')->nullable();       // null when the email is unknown
            $table->string('identifier');              // what they typed
            $table->enum('provider', ['password', 'google', 'facebook', 'telegram', 'apple'])
                ->default('password');
            $table->boolean('succeeded');
            $table->string('ip_hash', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('attempted_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // Unquoted identifiers so this parses on MySQL and PostgreSQL alike; both
        // support a DESC key part.
        DB::statement('CREATE INDEX idx_attempts_recent ON login_attempts (identifier, attempted_at DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
