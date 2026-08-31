<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Compliance;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompanyDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_directory_links_each_company_to_its_detail_page(): void
    {
        $company = $this->company();

        $this->actingAs($this->admin())
            ->get(route('companies'))
            ->assertOk()
            ->assertSee(route('companies.show', $company), false);
    }

    public function test_the_detail_page_shows_the_profile_licences_and_job_posts(): void
    {
        $company = $this->company();

        Compliance::forceCreate([
            'company_id' => $company->id,
            'name' => $company->name,
            'category' => 'Business Licence',
            'reference' => 'LIC-4471',
            'status' => Compliance::STATUS_VERIFIED,
            'expires_on' => '2027-01-17',
        ]);

        JobPost::forceCreate([
            'company_id' => $company->id,
            'company' => $company->name,
            'title' => 'Core Banking Engineer',
            'slug' => 'core-banking-engineer',
            'location' => 'Phnom Penh',
            'type' => 'Full-time',
            'status' => JobPost::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('companies.show', $company))
            ->assertOk()
            ->assertSee('PPCB Bank')
            ->assertSee('KH-CO-2026-0148')
            ->assertSee('LIC-4471')
            ->assertSee('Core Banking Engineer')
            ->assertSee('1/1 verified');
    }

    public function test_an_employer_cannot_open_another_companys_detail_page(): void
    {
        $company = $this->company();

        $employer = User::forceCreate([
            'first_name' => 'Sok',
            'last_name' => 'Dara',
            'email' => 'sok@example.com',
            'phone_number' => '+855 12 111 111',
            'role' => 'Employer',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($employer)
            ->get(route('companies.show', $company))
            ->assertForbidden();
    }

    private function company(): Company
    {
        return Company::forceCreate([
            'name' => 'PPCB Bank',
            'slug' => 'ppcb-bank',
            'registration_no' => 'KH-CO-2026-0148',
            'email' => 'careers@ppcbank.com.kh',
            'phone' => '+855 23 999 500',
            'industry' => 'Banking/ Insurance/ Microfinance',
            'status' => Company::STATUS_APPROVED,
        ]);
    }

    private function admin(): User
    {
        return User::forceCreate([
            'first_name' => 'Toeng',
            'last_name' => 'Bunsin',
            'email' => 'toeng@example.com',
            'phone_number' => '+855 12 000 000',
            'role' => 'Administrator',
            'password' => Hash::make('password123'),
        ]);
    }
}
