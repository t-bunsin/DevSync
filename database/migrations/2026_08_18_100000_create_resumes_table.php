<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Resumes
|
| One row per candidate CV, holding every section of the printed layout: the
| contact header, the professional summary, and the repeating blocks for work
| history, education, certifications, skills and languages.
|
| The repeating sections are JSON rather than child tables. They are an ordered
| list that is only ever written and read as part of the one document — nothing
| queries a single job or degree on its own — so a child table would buy joins
| and orphan cleanup for nothing. Same call, and the same `array` cast, as
| `job_posts.tabs`.
|
| `created_by` is a uuid, not a foreignId: module 01 replaced the legacy bigint
| users table with CHAR(36) UUID keys. Null on delete, so removing a staff
| account does not take the resumes they entered with it.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();

            $table->uuid('created_by')->nullable();

            // Contact header.
            $table->string('full_name');
            $table->string('headline', 120)->nullable();   // "Builder", the role it targets
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('location')->nullable();

            $table->text('summary')->nullable();           // professional summary

            // Repeating sections. See the note above on why these are JSON.
            $table->json('work_history')->nullable();
            $table->json('education')->nullable();
            $table->json('certifications')->nullable();
            $table->json('skills')->nullable();
            $table->json('languages')->nullable();

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamps();

            // Serves the status filter and the default newest-first ordering.
            $table->index(['status', 'created_at'], 'idx_resumes_status');

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
