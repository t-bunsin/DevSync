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
            'email' => 'admin@gmail.com',
            'password' => md5('12345678'), // Using md5 for password hashing
            'phone_number' => '1234567890' // Added phone number field
        ]);
    }
}
