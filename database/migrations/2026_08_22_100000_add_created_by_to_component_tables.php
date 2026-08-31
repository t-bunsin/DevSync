<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Who registered a component
|
| Records the signed-in admin who added a Location/Department/Job type, so
| the list can say who registered it and when — same idiom as job_posts'
| created_by (see add_created_by_to_job_posts_table).
|
| Nullable, and null on delete: the rows seeded when these tables were
| created have no admin behind them, and removing a staff account must not
| take the entries they added with it.
*/
return new class extends Migration
{
    private const TABLES = ['locations', 'departments', 'job_types'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('created_by')->nullable()->after('id');
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }
    }
};
