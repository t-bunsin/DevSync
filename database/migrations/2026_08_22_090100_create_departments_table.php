<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Departments
|
| Curated job-post departments, managed by admin under Component. Same
| relationship to job_posts.department as the locations table has to
| job_posts.location — see that migration's note.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('departments')->insert(
            collect(['Engineering', 'Sales & Marketing', 'Human Resources', 'Finance & Accounting', 'Customer Support', 'Operations'])
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
        Schema::dropIfExists('departments');
    }
};
