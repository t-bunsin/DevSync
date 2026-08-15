<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/*
| KH-WORKS | Companies become the hub
|
| Hangs job posts and compliance records off `companies`, and adopts every
| employer already named on a post as a real company record.
|
| Job posts and compliance keep their denormalised `company` / `name` string.
| The relation is the source of truth and the controllers rewrite the string
| from it on every save; keeping it means the public job views and the
| compliance list did not need rewriting, and a company rename still lands
| everywhere because it is re-copied on save.
|
| The foreign keys restrict rather than cascade: deleting an employer must not
| silently take its licences and live job adverts with it. The controller
| refuses the delete and says what is in the way.
*/
return new class extends Migration
{
    public function up(): void
    {
        $this->backfillCompaniesFromJobPosts();

        Schema::table('job_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
        });

        Schema::table('compliances', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->foreign('company_id')->references('id')->on('companies')->restrictOnDelete();
        });

        $this->linkJobPostsToCompanies();
    }

    public function down(): void
    {
        Schema::table('compliances', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

    }

    /**
     * Every distinct employer already named on a job post becomes a real
     * company, approved — those adverts are live, so the employer is real.
     */
    private function backfillCompaniesFromJobPosts(): void
    {
        $names = DB::table('job_posts')->distinct()->pluck('company')->filter();

        foreach ($names as $name) {
            $existing = DB::table('companies')->where('name', $name)->first();

            if ($existing) {
                DB::table('companies')->where('id', $existing->id)->update([
                    'slug' => $existing->slug ?: $this->uniqueSlug($name),
                    'status' => 'approved',
                ]);

                continue;
            }

            DB::table('companies')->insert([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Any company that predates this migration still needs a slug.
        foreach (DB::table('companies')->whereNull('slug')->get() as $company) {
            DB::table('companies')->where('id', $company->id)
                ->update(['slug' => $this->uniqueSlug($company->name)]);
        }
    }

    private function linkJobPostsToCompanies(): void
    {
        foreach (DB::table('companies')->get() as $company) {
            DB::table('job_posts')
                ->where('company', $company->name)
                ->update(['company_id' => $company->id]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (DB::table('companies')->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
};
