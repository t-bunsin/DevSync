<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | The employer's message to the candidate
|
| Deliberately a second column rather than opening up `note`. That one is
| labelled "Internal note — only the hiring team sees this" in the review
| dialog, and every note written so far was written on that promise; making it
| candidate-visible would publish candid assessments to the person they are
| about. So the private note stays private and this column carries whatever
| the employer chooses to say out loud.
|
| Nullable with no backfill: silence is the correct starting state, and no
| existing note is copied across.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->text('candidate_message')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('candidate_message');
        });
    }
};
