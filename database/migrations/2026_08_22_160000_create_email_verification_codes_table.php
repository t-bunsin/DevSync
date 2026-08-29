<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Registration email codes
|
| One row per account, replaced on every resend, so a code that was superseded
| stops working the moment the next one goes out.
|
| The code itself is never stored. It is a short-lived credential mailed to the
| address being proved, so only its hash is kept — the same reasoning as
| password_hash on users.
|
| attempts is the brute-force ceiling: six digits is a million combinations,
| which is plenty against a person and nothing against a script, so guesses are
| counted and the row is spent once the limit is reached.
|
| Every existing account is grandfathered as verified. They registered before
| this step existed, so marking them unverified would lock them out of their
| own accounts at the next login — the migration must not do that.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->id();
            // uuid(), not char(36): both render as char(36) on MySQL, but only
            // uuid() matches the type of users.id on PostgreSQL, which will not
            // carry a foreign key between char and uuid.
            $table->uuid('user_id');
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_codes');
    }
};
