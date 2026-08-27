<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobSeekerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'tbunsin002@gmail.com'],
            [
                'first_name' => 'Bunsin',
                'last_name' => 'Toeng',
                'display_name' => User::composeDisplayName('Bunsin', 'Toeng'),
                'phone' => '+855 12 000 002',
                'status' => User::STATUS_ACTIVE,
                'preferred_locale' => 'en',
                'password_hash' => '12345678',   // hashed by the model cast
            ]
        );

        // Employee alone is what isCandidateOnly() reads as a job seeker: no
        // admin and no employer role beside it. Same pair RegisterController
        // assigns when someone signs up without picking "employer".
        $user->syncRoles([Role::EMPLOYEE], Role::EMPLOYEE);

        // Not fillable, so it is set here rather than in the array above.
        // Without it the account is stuck on the mailed-code step and cannot
        // sign in, which would make the seed useless for local work.
        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }
    }
}
