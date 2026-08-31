<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | Subscriptions
|
| One row per user tracking which pricing tier (config/plans.php) they are
| on. No payment gateway is wired up yet — BillingController::store() writes
| this row directly once the mock "processing" step completes. `user_id` is
| unique because an account has exactly one active plan; upgrading or
| switching billing period updates the existing row rather than adding one.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_id');
            $table->string('plan_id');
            $table->enum('billing_period', ['monthly', 'annual'])->default('monthly');
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['active', 'canceled'])->default('active');
            $table->timestamp('started_at')->useCurrent();

            $table->timestamps();

            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
