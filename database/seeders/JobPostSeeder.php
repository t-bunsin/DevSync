<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Database\Seeder;

/**
 * Ports the three roles in config/jobs_demo.php into real job_posts rows.
 *
 * Those roles were the hardcoded fallback the public pages used before the job
 * post module existed; seeding them gives a new install a populated site that
 * is actually editable in the back office.
 *
 * Keyed on the slug through updateOrCreate, so re-running it refreshes the
 * three defaults instead of duplicating them, and leaves any other post alone.
 */
class JobPostSeeder extends Seeder
{
    public function run(): void
    {
        $demo = config('jobs_demo', []);

        if ($demo === []) {
            $this->command?->warn('config/jobs_demo.php is empty — no job posts seeded.');

            return;
        }

        foreach ($demo as $job) {
            $company = $this->company($job['company']);

            JobPost::updateOrCreate(
                ['slug' => $job['id']],
                [
                    'company_id' => $company->id,
                    'title' => $job['title'],
                    'company' => $company->name,
                    'location' => $job['location'],
                    'salary' => $job['salary'] ?? null,
                    'short_salary' => $job['short_salary'] ?? null,
                    'summary' => $job['summary'] ?? null,
                    'type' => $job['type'] ?? 'Full-time',
                    'mode' => $job['mode'] ?? 'On-site',
                    'experience' => $job['experience'] ?? null,
                    'department' => $job['department'] ?? null,

                    // The demo data holds rendered strings ("10 days left",
                    // "245 applicants", posted_days); the table holds the real
                    // date and number and re-renders those labels itself.
                    'deadline' => $this->deadlineDate($job['deadline'] ?? null),
                    'applicants' => $this->leadingInt($job['applicants'] ?? null),
                    'published_at' => now()->subDays((int) ($job['posted_days'] ?? 0)),

                    'logo' => $job['logo'] ?? 'default',
                    'featured' => (bool) ($job['featured'] ?? false),
                    'highlighted' => (bool) ($job['highlighted'] ?? false),
                    'status' => JobPost::STATUS_PUBLISHED,

                    'tabs' => $job['tabs'] ?? null,
                    'quick_apply_title' => $job['quick_apply']['title'] ?? null,
                    'quick_apply_text' => $job['quick_apply']['text'] ?? null,
                ]
            );
        }

        $this->command?->info('Seeded ' . count($demo) . ' job posts.');
    }

    /**
     * A post has to belong to a company, so the employer named in the demo data
     * is registered as one. Approved, because the advert is going live.
     */
    private function company(string $name): Company
    {
        return Company::firstOrCreate(
            ['name' => $name],
            [
                'slug' => Company::makeSlug($name),
                'status' => Company::STATUS_APPROVED,
            ]
        );
    }

    /** "10 days left" -> a date ten days out. Anything else leaves it open. */
    private function deadlineDate(?string $label): ?string
    {
        if ($label && preg_match('/(\d+)\s*day/i', $label, $matches)) {
            return now()->addDays((int) $matches[1])->toDateString();
        }

        return null;
    }

    /** "245 applicants" -> 245. */
    private function leadingInt(?string $label): int
    {
        return $label && preg_match('/(\d+)/', $label, $matches) ? (int) $matches[1] : 0;
    }
}
