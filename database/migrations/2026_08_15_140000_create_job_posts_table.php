<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Job posts
|
| The public job pages were served from config/jobs_demo.php. This table takes
| that over, and JobPost::toCatalogArray() reproduces the exact array shape the
| existing job views read, so nothing in the frontend had to be rewritten.
|
| Named `job_posts`, not `jobs`: Laravel's queue already owns a `jobs` table.
|
| `tabs` is JSON because the detail page renders three panels of the same
| shape (title, body, list_title, list) and flattening those into twelve
| columns buys nothing — nothing queries inside them.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();

            $table->string('slug')->unique();          // the catalog's `id`
            $table->string('title');
            $table->string('company');
            $table->string('location');

            $table->string('salary')->nullable();       // "$80,000 - $120,000 a year"
            $table->string('short_salary')->nullable(); // "$80k - $120k / year"
            $table->text('summary')->nullable();

            $table->string('type', 60)->default('Full-time');
            $table->string('mode', 60)->default('On-site');
            $table->string('experience', 60)->nullable();
            $table->string('department', 80)->nullable();

            $table->date('deadline')->nullable();
            $table->unsignedInteger('applicants')->default(0);

            // Drives the card artwork; the views switch on this keyword.
            $table->string('logo', 30)->default('default');

            $table->boolean('featured')->default(false);
            $table->boolean('highlighted')->default(false);

            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->json('tabs')->nullable();
            $table->string('quick_apply_title')->nullable();
            $table->string('quick_apply_text')->nullable();

            $table->timestamps();

            $table->index(['status', 'published_at'], 'idx_job_posts_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
