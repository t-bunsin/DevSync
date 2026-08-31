<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Candidate photo on a resume
|
| Path on the `public` disk, the same convention as compliance logos. Nullable:
| a photo is optional, and the register, preview and PDF all fall back to the
| initials monogram when there is none.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('resumes', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
