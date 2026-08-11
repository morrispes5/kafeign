<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds the single cashier/admin login used by the dashboard. Reuses
     * Laravel's default `users` table rather than a dedicated admin table
     * — there's no customer-account concept in this app to collide with.
     * Credentials come from .env (ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD).
     *
     * Deliberately no hardcoded password fallback here — this repo is
     * public, so a guessable default baked into source would just be a
     * published admin password for anyone who forgets to set .env. Fails
     * loudly instead, forcing a real value to be configured first.
     */
    public function run(): void
    {
        $email = config('kafeign.admin.email');
        $password = config('kafeign.admin.password');

        if (! $email || ! $password) {
            throw new RuntimeException(
                'ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env before running this seeder — see .env.example.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('kafeign.admin.name'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->command?->info("Admin user ready: {$email}");
    }
}
