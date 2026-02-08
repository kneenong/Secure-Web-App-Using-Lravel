<?php

// database/seeders/AdminUserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@secureapp.com',
            'password' => Hash::make('Admin@1234'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@secureapp.com',
            'password' => Hash::make('User@1234'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}