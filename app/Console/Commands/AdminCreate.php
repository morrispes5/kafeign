<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates an admin/cashier login, or resets the password of an existing
 * one, without anybody having to open tinker and hand-write an Eloquent
 * call (which is how an account previously got created outside .env and
 * then silently vanished on the next `migrate:fresh --seed`).
 */
class AdminCreate extends Command
{
    protected $signature = 'admin:create
                            {--email= : Email untuk login}
                            {--name= : Nama yang ditampilkan}
                            {--password= : Password baru (kosongkan untuk diketik aman saat diminta)}';

    protected $description = 'Buat akun admin baru, atau ganti password akun admin yang sudah ada';

    public function handle(): int
    {
        $email = $this->option('email') ?: text(
            label: 'Email untuk login',
            placeholder: 'nama@contoh.com',
            required: true,
        );

        $existing = User::where('email', $email)->first();

        if ($existing) {
            $this->line("Akun <options=bold>{$email}</> sudah ada — passwordnya akan diganti.");
        }

        $name = $this->option('name')
            ?: ($existing?->name ?? text(label: 'Nama admin', default: 'Kafeign Admin', required: true));

        $plainPassword = $this->option('password') ?: password(
            label: 'Password baru',
            hint: 'Minimal 8 karakter. Tidak akan terlihat saat diketik.',
            required: true,
        );

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $plainPassword],
            [
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($existing?->id)],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($plainPassword),
                'email_verified_at' => now(),
            ],
        );

        $this->newLine();
        $this->components->info($existing
            ? "Password untuk {$email} berhasil diganti."
            : "Akun admin {$email} berhasil dibuat.");
        $this->line('  Login di: <options=bold>/admin/login</>');

        // An account created here lives only in the database, so a later
        // `migrate:fresh --seed` would drop it unless .env knows about it.
        if (config('kafeign.admin.email') !== $email) {
            $this->newLine();
            $this->components->warn('Akun ini belum tercatat di .env.');
            $this->line("  Kalau nanti kamu jalankan <options=bold>php artisan migrate:fresh --seed</>,");
            $this->line('  akun ini akan HILANG karena database di-reset total.');
            $this->line("  Supaya aman, set di .env: <options=bold>ADMIN_EMAIL={$email}</>");
        }

        return self::SUCCESS;
    }
}
