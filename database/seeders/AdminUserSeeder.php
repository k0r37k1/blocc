<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user from environment variables.
     * Idempotent: updates existing or creates new.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@kopfsalat.blog')],
            [
                'username' => env('ADMIN_USERNAME', 'admin'),
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme')),
                'must_change_credentials' => true,
            ]
        );
    }
}
