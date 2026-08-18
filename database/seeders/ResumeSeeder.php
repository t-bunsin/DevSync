<?php

namespace Database\Seeders;

use App\Models\Resume;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * One sample resume, filled in end to end so every section of the register,
 * the edit form and the printed preview has something to show on a fresh
 * install — including the two-column skills list and the language bars, which
 * render as empty blocks when the section is missing.
 *
 * Seeded as published, and attributed to an admin where one exists, so the
 * list shows a real registrant rather than "Unknown".
 *
 * Keyed on email, so re-running refreshes this row instead of filing the same
 * candidate again.
 */
class ResumeSeeder extends Seeder
{
    public const EMAIL = 'Olivia.Martinez@example.com';

    public function run(): void
    {
        $admin = User::whereHas('roles', fn ($query) => $query->where('code', Role::ADMIN))->first();

        /*
         * forceFill, not updateOrCreate: the JSON sections and created_by are
         * deliberately left out of $fillable so a crafted request cannot mass
         * assign them. Seed data is trusted, so it writes them directly.
         */
        Resume::firstOrNew(['email' => self::EMAIL])
            ->forceFill([
                'full_name' => 'Olivia Martinez',
                'headline' => 'Builder',
                'phone' => '(555)555-5555',
                'location' => 'Hillcrest, NY',
                'status' => Resume::STATUS_PUBLISHED,
                'created_by' => $admin?->id,

                'summary' => 'Highly skilled builder with 8 years in construction. Proven record in '
                    . 'reducing costs, enhancing efficiency, and delivering quality projects. '
                    . 'Expertise in leadership, project management, and safety.',

                // Months are stored as the "YYYY-MM" the month inputs post;
                // Resume::formatMonth() turns them into 01/2021 for the preview.
                'work_history' => [
                    [
                        'role' => 'Builder',
                        'company' => 'GreenBuild Constructors',
                        'location' => 'Hillcrest, NY',
                        'started_on' => '2021-01',
                        'ended_on' => '2025-09',
                        'bullets' => [
                            'Managed construction sites with 20% efficiency rise',
                            'Oversaw 15 projects annually enhancing quality',
                            'Reduced project costs by 25% through strategic sourcing',
                        ],
                    ],
                    [
                        'role' => 'Construction Supervisor',
                        'company' => 'EcoStone Developments',
                        'location' => 'Albany, NY',
                        'started_on' => '2017-06',
                        'ended_on' => '2020-12',
                        'bullets' => [
                            'Improved team productivity by 30% with training',
                            'Completed 10 commercial projects below budget',
                            'Enhanced safety compliance by 40% at site',
                        ],
                    ],
                    [
                        'role' => 'Site Foreman',
                        'company' => 'ProBuild Industries',
                        'location' => 'New York, NY',
                        'started_on' => '2013-09',
                        'ended_on' => '2017-05',
                        'bullets' => [
                            'Led a crew of 12 across residential builds',
                            'Cut material waste by 15% with tighter scheduling',
                            'Kept 4 consecutive projects on their delivery date',
                        ],
                    ],
                ],

                'skills' => [
                    'Project Management',
                    'Site Supervision',
                    'Blueprint Reading',
                    'Safety Compliance',
                    'Team Leadership',
                    'Budget Management',
                    'Construction Techniques',
                    'Quality Assurance',
                ],

                'certifications' => [
                    [
                        'name' => 'Certified Construction Manager',
                        'issuer' => 'Construction Management Association of America',
                    ],
                    [
                        'name' => 'LEED Accredited Professional',
                        'issuer' => 'U.S. Green Building Council',
                    ],
                ],

                'education' => [
                    [
                        'degree' => 'Master of Architecture',
                        'field' => 'Construction Management',
                        'institution' => 'Illinois Institute of Technology',
                        'location' => 'Chicago, Illinois',
                        'graduated_on' => '2013-05',
                    ],
                    [
                        'degree' => 'Bachelor of Science',
                        'field' => 'Civil Engineering',
                        'institution' => 'University of Illinois',
                        'location' => 'Champaign, Illinois',
                        'graduated_on' => '2011-05',
                    ],
                ],

                'languages' => [
                    ['name' => 'Spanish', 'level' => 'Beginner (A1)'],
                    ['name' => 'German', 'level' => 'Beginner (A1)'],
                    ['name' => 'French', 'level' => 'Beginner (A1)'],
                ],
            ])
            ->save();

        $this->installPhoto(Resume::where('email', self::EMAIL)->first());

        $this->command?->info('Seeded 1 resume for Olivia Martinez.');
    }

    /**
     * Copies the bundled placeholder portrait onto the public disk, mirroring
     * CompanySeeder::installArtwork(). Skipped when the resume already points
     * at a file that exists, so a real upload survives a re-run.
     *
     * The asset is a generic silhouette rather than a photograph of a person —
     * seed data ships with the repo, and a real face does not belong in it.
     */
    private function installPhoto(Resume $resume): void
    {
        if ($resume->photo && Storage::disk('public')->exists($resume->photo)) {
            return;
        }

        $source = __DIR__ . '/assets/resume-photo.jpg';

        if (! is_file($source)) {
            $this->command?->warn("Seed asset missing: {$source}");

            return;
        }

        $target = 'resume-photos/sample-resume-photo.jpg';

        Storage::disk('public')->put($target, file_get_contents($source));

        $resume->forceFill(['photo' => $target])->save();
    }
}
