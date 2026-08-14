<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'display_name' => 'bunsin toeng',
                'phone' => '1234567890',
                'status' => User::STATUS_ACTIVE,
                'preferred_locale' => 'en',
                'password_hash' => '12345678',   // hashed by the model cast
            ]
        );

        $user->syncRoles([Role::ADMIN], Role::ADMIN);
    }
}
