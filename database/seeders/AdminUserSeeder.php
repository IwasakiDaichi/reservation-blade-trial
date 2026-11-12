<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'cpdo.admin@example.com'],
            [
                'name' => 'CPDO Administrator',
                'password' => Hash::make(
                    env('CPDO_ADMIN_PASSWORD', 'admin1234')
                ),
                'role' => 'cpdo_admin',
                'email_verified_at' => now(),
            ]
        );
    }
}