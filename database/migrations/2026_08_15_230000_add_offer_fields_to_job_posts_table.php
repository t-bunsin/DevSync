<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| The "What we can offer" block on a job advert: benefits, highlights and
| career opportunities.
|
| Stored as text, one item per line, the same way the tab bullet lists are
| authored. Nothing queries inside them, and an employer writes them as a list
| rather than as structured records.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->text('benefits')->nullable()->after('quick_apply_text');
            $table->text('highlights')->nullable()->after('benefits');
            $table->text('career_opportunities')->nullable()->after('highlights');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['benefits', 'highlights', 'career_opportunities']);
        });
    }
};
