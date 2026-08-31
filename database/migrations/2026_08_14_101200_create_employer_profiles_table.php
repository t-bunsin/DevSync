<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | PLACEHOLDER — not part of the module 01 schema
|
| Module 01 keeps `users` deliberately thin: "anything specific to being an
| employer or a job seeker lives in that role's own profile table, not here."
| Those profile tables belong to a later module that does not exist yet, but
| the registration form already collects company_name from employers and one
| existing account carries a value, so it needs somewhere to live today.
|
| This is the smallest table that keeps that working. Replace it with the real
| employer profile table when the company/job module lands, and move this
| column across rather than adding more fields here.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->string('company_name');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_profiles');
    }
};
