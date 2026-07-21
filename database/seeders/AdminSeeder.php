<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Create or update the default admin account.
     *
     * Override credentials with ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD in .env.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );
    }
}
