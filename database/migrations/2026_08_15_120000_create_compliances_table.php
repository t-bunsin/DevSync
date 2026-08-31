<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Compliance records
|
| One row per document an employer has to hold to trade on the platform — a
| business licence, a tax certificate, a labour registration. Each starts
| `pending` and an admin either verifies or rejects it; only a verified record
| earns the blue badge in the UI.
|
| BIGINT id to match `companies`, but verified_by is a UUID because that is
| what module 01 made `users.id`.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliances', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('category', 80);
            $table->string('reference', 120)->nullable();
            $table->string('logo')->nullable();          // path on the `public` disk

            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();

            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->uuid('verified_by')->nullable();

            $table->timestamps();

            // Serves both the status filter on the index and the expiry sweep.
            $table->index(['status', 'expires_on'], 'idx_compliances_status');

            // Null rather than cascade: losing the admin who signed something
            // off must not delete the record of the sign-off.
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliances');
    }
};
