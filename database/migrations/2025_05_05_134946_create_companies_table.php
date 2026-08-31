<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Companies
|
| The employer record. Job posts and compliance records both belong to one,
| and employer signup registers against one.
|
| The whole shape lives here. It was previously spread over three follow-up
| migrations (status/slug, cover, then the profile fields) — those were folded
| back in so a fresh install builds the table in one step and the columns can
| be read in one place.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('registration_no', 120)->nullable();

            // Employer facts, shown as "Employer Details" on the public pages
            $table->string('employer_type', 80)->nullable();
            $table->string('industry', 120)->nullable();
            $table->string('employee_count', 40)->nullable();

            // Long-form profile, shown on the Company tab of every advert
            $table->text('description')->nullable();
            $table->text('vision_mission')->nullable();
            $table->text('what_we_do')->nullable();
            $table->text('why_join_us')->nullable();
            $table->text('workplace_culture')->nullable();

            // Employer signup can register a company no admin has vetted yet,
            // so a company starts pending unless an admin created it.
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Artwork: logo is a square mark, cover a wide banner. A company
            // may have either, both, or neither.
            $table->string('logo')->nullable();
            $table->string('cover')->nullable();

            // Contact
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();

            $table->index(['status'], 'idx_companies_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
