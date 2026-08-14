<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Module 01 — Identity and Access (2 of 10)
|
| Roles are reference data, seeded once here so the codes exist before
| user_roles can point at them. SMALLSERIAL -> smallIncrements (UNSIGNED
| SMALLINT AUTO_INCREMENT); every FK to this table must be unsignedSmallInteger.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('code', 30)->unique();      // 'admin' | 'employer' | 'employee'
            $table->string('name_en', 60);
            $table->string('name_km', 120)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);   // system roles cannot be deleted in the UI
            $table->smallInteger('sort_order')->default(0);
        });

        DB::table('roles')->insert([
            [
                'code' => 'admin',
                'name_en' => 'Administrator',
                'name_km' => 'អ្នកគ្រប់គ្រង',
                'description' => 'Full platform access and moderation',
                'sort_order' => 1,
            ],
            [
                'code' => 'employer',
                'name_en' => 'Employer',
                'name_km' => 'និយោជក',
                'description' => 'Posts jobs and reviews applicants',
                'sort_order' => 2,
            ],
            [
                'code' => 'employee',
                'name_en' => 'Job seeker',
                'name_km' => 'អ្នកស្វែងរកការងារ',
                'description' => 'Builds a profile and applies to jobs',
                'sort_order' => 3,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
