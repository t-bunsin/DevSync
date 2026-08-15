<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * One fully filled-in company, so a fresh install has a realistic employer to
 * file compliance against and post jobs for.
 *
 * Keyed on the name through updateOrCreate: JobPostSeeder registers the same
 * employer as a bare name/slug row, and this fills in the rest rather than
 * creating a duplicate — whichever order the two run in.
 *
 * The logo and cover are copied out of database/seeders/assets into the public
 * disk, so a fresh install has real artwork without anyone uploading it.
 */
class CompanySeeder extends Seeder
{
    public const COMPANY_NAME = 'PPCB Bank';

    public function run(): void
    {
        $company = Company::updateOrCreate(
            ['name' => self::COMPANY_NAME],
            [
                'slug' => Company::makeSlug(self::COMPANY_NAME),
                'registration_no' => 'KH-CO-2026-0148',
                'status' => Company::STATUS_APPROVED,

                'employer_type' => 'Direct Employer',
                'industry' => 'Banking/ Insurance/ Microfinance',
                'employee_count' => '501 to 1000',

                'email' => 'careers@ppcbank.com.kh',
                'phone' => '+855 23 999 500',
                'website' => 'https://www.ppcbank.com.kh',
                'address' => 'No.217, Norodom Blvd, Sangkat Tonle Bassac, Khan Chamkamorn, Phnom Penh, Cambodia',

                'description' => 'A commercial local bank operating in Cambodia since 2008, known for accessible services and a strong digital banking offer.',

                'vision_mission' => <<<'TEXT'
                    PPCBank is commercial local bank which had operation in Cambodia since 01 September 2008.
                    PPCBank has 26 Branches in Cambodia now.

                    Slogan: We make banking easy.

                    Vision: Creating a success story in Southeast Asia in the financial industry.

                    To create a space which we build with our most valued customers through closeness and trust. We have also embedded the concept of 'leaping forward' in our symbol, which signifies sustainably growing through change and innovation.
                    TEXT,

                'what_we_do' => <<<'TEXT'
                    We warmly welcome you into our big family to share in our vision to creating a success story in Southeast Asia in the financial industry. We want you to feel proud about the opportunities with us, because we can assure you that you will always be well taken care of, as we boost your personal and professional development.

                    Do you think you have what it takes to be part of us? Join us now!
                    TEXT,

                'why_join_us' => <<<'TEXT'
                    We are the high professional standard in Banking Industrial.

                    The Bank has provide many benefits to staff such as:

                    - Basic Salary is very competitive
                    - Lunch Allowance
                    - Language Allowance (Chinese, Korean and Japanese)
                    - Khmer New Year Bonus
                    - Pchum Ben Bonus
                    - Seniority Payment
                    - Branch/Department KPI Incentive provided Monthly, Quarterly and Yearly
                    - NSSF and Private Insurance
                    TEXT,

                'workplace_culture' => <<<'TEXT'
                    PPCBank is commercial local bank which had operation in Cambodia since 01 September 2008.
                    PPCBank has 26 Branches in Cambodia now.
                    PPCBank is high standard working culture of Korean Banking Industrial.

                    PPCBank will provide the chance for our PPCBank Staff to show their potential and high commitment culture.
                    TEXT,
            ]
        );

        $this->installArtwork($company);

        $this->command?->info("Seeded company: {$company->name}.");
    }

    /**
     * Copies the bundled logo and cover onto the public disk. Skipped when the
     * company already points at a file that exists, so a real upload is never
     * overwritten by a re-run.
     */
    private function installArtwork(Company $company): void
    {
        $assets = [
            'logo' => ['companies/ppcbank-logo.png', __DIR__ . '/assets/ppcbank-logo.png'],
            'cover' => ['companies/ppcbank-cover.jpg', __DIR__ . '/assets/ppcbank-cover.jpg'],
        ];

        foreach ($assets as $column => [$target, $source]) {
            if ($company->{$column} && Storage::disk('public')->exists($company->{$column})) {
                continue;
            }

            if (! is_file($source)) {
                $this->command?->warn("Seed asset missing: {$source}");

                continue;
            }

            Storage::disk('public')->put($target, file_get_contents($source));
            $company->{$column} = $target;
        }

        $company->save();
    }
}
