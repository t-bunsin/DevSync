<?php

namespace Database\Seeders;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Order matters: compliance needs a company, and both the compliance
        // sign-off and the resume need an admin to attribute them to.
        $this->call(UserSeeder::class);
        $this->call(JobSeekerSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(JobPostSeeder::class);
        $this->call(ComplianceSeeder::class);
        $this->call(ResumeSeeder::class);
    }
}
