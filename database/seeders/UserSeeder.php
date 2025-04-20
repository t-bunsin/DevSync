<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'bunsin',
            'last_name' => 'toeng',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678'),
        ]);
    }
}
