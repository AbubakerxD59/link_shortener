<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Remove existing admins and create the default admin account.
     *
     * Override credentials with ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD in .env.
     */
    public function run(): void
    {
        User::query()->where('is_admin', true)->delete();

        User::query()->create([
            'name' => env('ADMIN_NAME', 'Admin'),
            'email' => env('ADMIN_EMAIL', 'admin.shrtnlnk@gmail.com'),
            'password' => env('ADMIN_PASSWORD', '9K}H$a2..rH,QpS8,0'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }
}
