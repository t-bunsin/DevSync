<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | When the employer last moved an application
|
| The candidate's application page wants to say "Accepted on 12 Aug" or
| "Rejected on 12 Aug", and nothing on the row could answer that: updated_at
| moves whenever the employer edits their private note, so it dates the last
| edit, not the decision.
|
| Backfill is the best evidence available rather than the truth — for rows
| already past 'new', updated_at is the closest stamp there is. Anything still
| sitting on 'new' has had no decision, so it stays null.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->timestamp('status_changed_at')->nullable()->after('status');
        });

        DB::table('job_applications')
            ->where('status', '!=', 'new')
            ->update(['status_changed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('status_changed_at');
        });
    }
};
