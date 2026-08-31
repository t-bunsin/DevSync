<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Notifications
|
| Laravel's standard database-channel table, with one adaptation: the
| notifiable is addressed by `uuidMorphs`, not `morphs`. Module 01 replaced the
| legacy bigint users table with CHAR(36) UUID keys, so the default BIGINT
| column could not hold the id of the staff account being notified.
|
| First use: a new job application, which lands in the Activity center bell in the
| admin shell — see App\Notifications\NewJobApplication.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Serves the unread badge and the newest-first dropdown.
            $table->index(['notifiable_id', 'read_at'], 'idx_notifications_unread');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
