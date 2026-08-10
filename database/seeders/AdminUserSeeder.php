<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds the single cashier/admin login used by the dashboard. Reuses
     * Laravel's default `users` table rather than a dedicated admin table
     * — there's no customer-account concept in this app to collide with.
     * Credentials come from .env (ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD)
     * so nothing sensitive is hardcoded in source.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@kafeign.test');
        $password = env('ADMIN_PASSWORD', 'change-me-please');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Kafeign Admin'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info("Admin user ready: {$email}");
    }
}
