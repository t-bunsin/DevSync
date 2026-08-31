<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Job Types
|
| Curated job-post types, managed by admin under Component. Replaces the
| hardcoded list JobPost::types() used to return — see that method, now
| backed by this table. job_posts.type is still plain text, so existing
| posts are unaffected by edits made here.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('job_types')->insert(
            collect(['Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary'])
                ->map(fn (string $name) => [
                    'name' => $name,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('job_types');
    }
};
