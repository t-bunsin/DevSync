<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| ZIN-WORKS | PayWay fields on subscriptions
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
        $this->setStatusValues(['pending', 'active', 'canceled', 'failed'], 'pending');

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

        $this->setStatusValues(['active', 'canceled'], 'active');
    }

    /**
     * Rewrite the allowed values of `subscriptions.status`.
     *
     * MySQL gets a MODIFY rather than a Blueprint change(), so this doesn't need
     * doctrine/dbal just to widen an enum. PostgreSQL has no ENUM behind
     * $table->enum() — Laravel renders it as a varchar plus a CHECK constraint —
     * so there the constraint is swapped and the default reset instead.
     */
    private function setStatusValues(array $values, string $default): void
    {
        $quoted = implode(', ', array_map(fn ($value) => "'{$value}'", $values));

        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            DB::statement(
                "ALTER TABLE subscriptions MODIFY status ENUM({$quoted}) NOT NULL DEFAULT '{$default}'"
            );

            return;
        }

        // Drop by lookup, not by name: the constraint Laravel's enum() leaves
        // behind is auto-named by PostgreSQL, and that name is not contractual.
        DB::statement(<<<'SQL'
            DO $$
            DECLARE constraint_name text;
            BEGIN
                FOR constraint_name IN
                    SELECT con.conname
                    FROM pg_constraint con
                    JOIN pg_class rel ON rel.oid = con.conrelid
                    JOIN pg_attribute att ON att.attrelid = rel.oid AND att.attnum = ANY (con.conkey)
                    WHERE rel.relname = 'subscriptions'
                      AND con.contype = 'c'
                      AND att.attname = 'status'
                LOOP
                    EXECUTE format('ALTER TABLE subscriptions DROP CONSTRAINT %I', constraint_name);
                END LOOP;
            END $$;
        SQL);

        DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status IN ({$quoted}))");
        DB::statement("ALTER TABLE subscriptions ALTER COLUMN status SET DEFAULT '{$default}'");
        DB::statement('ALTER TABLE subscriptions ALTER COLUMN status SET NOT NULL');
    }
};
