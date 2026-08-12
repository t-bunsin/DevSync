<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registration now asks whether the account belongs to a job seeker or an
     * employer, so both need somewhere to live on the user record.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('account_type', ['employee', 'employer'])->default('employee')->after('role');
            $table->string('company_name')->nullable()->after('account_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_type', 'company_name']);
        });
    }
};
