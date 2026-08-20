<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Job applications
|
| One row per candidate application to a job post. This table is what finally
| *defines* "how many candidates applied": until now `job_posts.applicants` was
| a number typed in by hand on the post form, and the public apply dialog only
| drew a success message on the client — nothing was ever recorded.
|
| The candidate's details are copied onto the row rather than read back through
| `user_id` alone. An application is a record of what was sent on the day it was
| sent, so a later profile rename must not rewrite the employer's inbox, and a
| closed account must not empty it (hence nullOnDelete rather than cascade).
|
| `resume_id` is the CV attached at apply time, when the candidate has one on
| the platform; the detail page offers its download link straight from there.
|
| UNIQUE (job_post_id, user_id) keeps one application per account per post, so
| a double-submitted form cannot inflate the count. MySQL treats NULLs as
| distinct, so guest rows — should applying ever open up to them — are unaffected.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_post_id')->constrained('job_posts')->cascadeOnDelete();
            $table->uuid('user_id')->nullable();
            $table->foreignId('resume_id')->nullable()->constrained('resumes')->nullOnDelete();

            // The details as submitted. See the note above on why they are copied.
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->text('message')->nullable();

            $table->enum('status', ['new', 'reviewing', 'shortlisted', 'rejected', 'hired'])
                ->default('new');
            $table->text('note')->nullable();

            $table->timestamp('applied_at')->useCurrent();
            $table->timestamps();

            // Serves both the per-post listing and its status filter.
            $table->index(['job_post_id', 'status'], 'idx_job_applications_post_status');
            $table->unique(['job_post_id', 'user_id'], 'uniq_job_applications_post_user');

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
