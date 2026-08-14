<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Module 01 — Identity and Access (4 of 10)
|
| Optional today; needed once admins are split by duty. Left unseeded on
| purpose — permission codes ('job.approve', 'company.verify') belong to the
| modules that enforce them, not to identity.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 60)->unique();      // 'job.approve', 'company.verify'
            $table->text('description')->nullable();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('role_id');
            $table->unsignedInteger('permission_id');

            $table->primary(['role_id', 'permission_id']);

            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
