<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Compliance;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * One compliance record, filed against the company CompanySeeder registers.
 *
 * Seeded as verified so the blue badge is visible on a fresh install, and
 * stamped to an admin because a sign-off with nobody behind it would be a lie
 * the UI then reports as fact. With no admin present it stays pending.
 *
 * Keyed on company + reference, so re-running refreshes the row rather than
 * filing the same licence twice.
 */
class ComplianceSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('name', CompanySeeder::COMPANY_NAME)->first();

        if (! $company) {
            $this->command?->warn('CompanySeeder has not run — no compliance record seeded.');

            return;
        }

        $admin = User::whereHas('roles', fn ($query) => $query->where('code', Role::ADMIN))->first();

        Compliance::updateOrCreate(
            [
                'company_id' => $company->id,
                'reference' => 'KH-BL-2026-0148',
            ],
            [
                'name' => $company->name,
                'category' => 'Business Licence',
                'status' => $admin ? Compliance::STATUS_VERIFIED : Compliance::STATUS_PENDING,
                'issued_on' => now()->subMonths(7)->toDateString(),
                'expires_on' => now()->addMonths(5)->toDateString(),
                'notes' => 'Checked against the Ministry of Commerce register.',
                'verified_at' => $admin ? now() : null,
                'verified_by' => $admin?->id,
            ]
        );

        $this->command?->info("Seeded 1 compliance record for {$company->name}.");
    }
}
