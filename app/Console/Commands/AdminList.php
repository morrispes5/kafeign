<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Answers "which email do I actually log in with?" without a trip to
 * tinker or the SQLite file.
 */
class AdminList extends Command
{
    protected $signature = 'admin:list';

    protected $description = 'Tampilkan semua akun admin yang bisa login ke dashboard';

    public function handle(): int
    {
        $users = User::orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->components->warn('Belum ada akun admin sama sekali.');
            $this->line('  Buat satu dengan: <options=bold>php artisan admin:create</>');

            return self::SUCCESS;
        }

        $configuredEmail = config('kafeign.admin.email');

        $this->newLine();
        $this->table(
            ['ID', 'Nama', 'Email', 'Dibuat', 'Tercatat di .env?'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->created_at?->format('d M Y H:i') ?? '-',
                // Anything not in .env is database-only, so a
                // `migrate:fresh --seed` would silently delete it.
                $user->email === $configuredEmail ? 'ya (aman)' : 'TIDAK — hilang kalau DB di-reset',
            ])->all(),
        );

        $this->line('  Lupa password? <options=bold>php artisan admin:create</> (pakai email yang sama)');
        $this->newLine();

        return self::SUCCESS;
    }
}
