<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | The CV attached to an application
|
| Applying now requires one, by either route: the candidate uploads a file, or
| they build a resume in the dashboard and `resume_id` carries it. This column
| holds the uploaded case.
|
| Stored on the `local` disk, not `public`: a CV is the candidate's own contact
| details and history, and a file under public/storage is served to anyone who
| can guess its path. It is read back through an authenticated download route
| instead — see JobApplicationController::downloadCv().
|
| `cv_name` keeps the name the candidate's file had. The stored name is a
| random hash, so without this the employer downloads "9f2c…pdf".
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('cv_path')->nullable()->after('resume_id');
            $table->string('cv_name')->nullable()->after('cv_path');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['cv_path', 'cv_name']);
        });
    }
};
