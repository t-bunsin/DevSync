<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | PayWay fields on subscriptions
|
| Checking out now goes through ABA PayWay (Bakong/KHQR) instead of
| activating instantly, so a subscription needs to exist in a 'pending'
| state while payment is in flight, and 'failed' when PayWay reports it
| didn't go through. `tran_id` is the id this app generates and sends to
| PayWay; the callback looks the subscription up by it.
*/
return new class extends Migration
{
    public function up(): void
    {
        // MODIFY rather than a Blueprint change(), so this doesn't need
        // doctrine/dbal just to widen an enum.
        DB::statement(
            "ALTER TABLE subscriptions MODIFY status ENUM('pending', 'active', 'canceled', 'failed') NOT NULL DEFAULT 'pending'"
        );

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('tran_id')->nullable()->unique()->after('user_id');
            $table->string('payment_option')->nullable()->after('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['tran_id', 'payment_option']);
        });

        DB::statement(
            "ALTER TABLE subscriptions MODIFY status ENUM('active', 'canceled') NOT NULL DEFAULT 'active'"
        );
    }
};
