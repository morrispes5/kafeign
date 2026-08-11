<?php

namespace App\Console\Commands;

use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

use function Laravel\Prompts\text;

/**
 * Escape hatch for the login throttle: five wrong passwords locks the
 * form for a minute, which is correct against an attacker but merely
 * infuriating when it is the actual cashier who mistyped. This clears
 * the lockout immediately from the terminal.
 */
class AdminUnlock extends Command
{
    protected $signature = 'admin:unlock
                            {--email= : Batasi hanya ke email ini}
                            {--ip=127.0.0.1 : IP yang terkunci (default: komputer ini)}
                            {--all : Hapus semua kunci login, tanpa peduli email/IP}';

    protected $description = 'Buka kunci login admin yang kena batas percobaan (rate limit)';

    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->clearAll();
        }

        $email = $this->option('email') ?: text(
            label: 'Email yang terkunci',
            placeholder: 'nama@contoh.com',
            required: true,
        );

        $key = LoginRequest::throttleKeyFor($email, (string) $this->option('ip'));
        RateLimiter::clear($key);

        $this->components->info("Kunci login untuk {$email} sudah dibuka. Silakan coba login lagi.");
        $this->line('  Kalau masih terkunci, kemungkinan IP-nya berbeda — pakai: <options=bold>php artisan admin:unlock --all</>');

        return self::SUCCESS;
    }

    /**
     * The throttle key includes the client IP, which the console has no
     * reliable way to know (a phone on the cafe wifi is not 127.0.0.1).
     * Rather than guess, --all wipes every rate-limiter entry straight
     * out of the cache store.
     */
    private function clearAll(): int
    {
        if (config('cache.default') !== 'database') {
            $this->components->warn('Cache driver bukan "database", jadi tidak bisa dihapus selektif.');
            $this->line('  Jalankan ini sebagai gantinya: <options=bold>php artisan cache:clear</>');

            return self::SUCCESS;
        }

        $table = config('cache.stores.database.table', 'cache');
        $prefix = config('cache.prefix');

        // RateLimiter stores each attempt counter under the throttle key
        // itself plus a companion ":timer" entry; both carry the cache
        // prefix. Matching on the "|" that separates email from IP keeps
        // this from touching unrelated cached values.
        $deleted = DB::table($table)
            ->where('key', 'like', $prefix.'%|%')
            ->delete();

        $this->components->info("{$deleted} entri kunci login dihapus. Semua akun bisa login lagi sekarang.");

        return self::SUCCESS;
    }
}
