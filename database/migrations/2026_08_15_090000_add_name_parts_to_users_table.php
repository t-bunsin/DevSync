<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| Store the name in parts.
|
| Module 01 kept a single `display_name`, which meant the signup form had to
| join first and last name together and throw the split away. Both parts are
| stored now; `display_name` stays as the one label the rest of the app reads
| and is kept in step with the parts by the User model.
|
| Nullable because a social-only or phone-only account may never supply a name,
| the same reason `display_name` is nullable. 80 chars each matches the limit
| the registration form already validates against.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 80)->nullable()->after('status');
            $table->string('last_name', 80)->nullable()->after('first_name');
        });

        $this->backfillFromDisplayName();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }

    /**
     * Existing rows only have the joined name, so split it on the first run of
     * whitespace: what comes before is the first name, the remainder is the
     * last name. A single-word display_name leaves last_name null rather than
     * guessing at one.
     *
     * Done in PHP rather than SUBSTRING_INDEX/CHAR_LENGTH so the migration also
     * runs on the SQLite connection the test suite uses.
     */
    private function backfillFromDisplayName(): void
    {
        DB::table('users')
            ->select('id', 'display_name')
            ->whereNotNull('display_name')
            ->orderBy('id')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $parts = preg_split('/\s+/u', trim($row->display_name), 2, PREG_SPLIT_NO_EMPTY) ?: [];

                    if ($parts === []) {
                        continue;
                    }

                    DB::table('users')->where('id', $row->id)->update([
                        'first_name' => mb_substr($parts[0], 0, 80),
                        'last_name' => isset($parts[1]) ? mb_substr($parts[1], 0, 80) : null,
                    ]);
                }
            });
    }
};
