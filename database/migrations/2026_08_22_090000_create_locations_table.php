<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Locations
|
| Curated job-post locations, managed by admin under Component. Job posts
| still store the chosen name as plain text (job_posts.location is unchanged)
| — this table only supplies the options offered on the post form, so
| existing posts keep whatever they already had even if it's since been
| renamed or removed here.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('locations')->insert(
            collect(['Phnom Penh', 'Siem Reap', 'Sihanoukville', 'Battambang', 'Remote'])
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
        Schema::dropIfExists('locations');
    }
};
