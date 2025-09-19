<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
        'first_name' => 'bunsin',
        'last_name' => 'toeng',
        'email' => 'admin@gmail.com',
        'password'=> bcrypt('12345678'),
        'phone_number' => '1234567890',
        'role' => 'user'
        ]);
    }
}
