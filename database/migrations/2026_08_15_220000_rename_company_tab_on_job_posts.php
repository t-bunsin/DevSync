<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| The third authored panel on a job post was `company`. The Company tab now
| renders the employer's own profile, so that panel became extra job copy and
| moved under Requirements — renamed here so existing posts keep their content
| instead of silently losing it.
*/
return new class extends Migration
{
    public function up(): void
    {
        $this->renameKey('company', 'job_description');
    }

    public function down(): void
    {
        $this->renameKey('job_description', 'company');
    }

    private function renameKey(string $from, string $to): void
    {
        foreach (DB::table('job_posts')->select('id', 'tabs')->get() as $post) {
            $tabs = json_decode((string) $post->tabs, true);

            if (! is_array($tabs) || ! array_key_exists($from, $tabs)) {
                continue;
            }

            $tabs[$to] = $tabs[$from];
            unset($tabs[$from]);

            DB::table('job_posts')->where('id', $post->id)->update([
                'tabs' => json_encode($tabs),
            ]);
        }
    }
};
