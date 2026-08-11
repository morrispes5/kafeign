<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * How many failed attempts before the login form locks up, and for
     * how long. Deliberately low: this app has exactly one admin account,
     * so a human typo budget of five is plenty, while an automated
     * password-guessing run gets throttled to a crawl.
     */
    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_SECONDS = 60;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * Rejects the attempt outright once too many failures pile up for
     * this email + IP combination. Keyed on both so that one person
     * fat-fingering their password can't lock out the whole cafe, and a
     * single attacker can't dodge the limit just by varying the email.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), self::LOCKOUT_SECONDS);
    }

    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->throttleKey());
    }

    private function throttleKey(): string
    {
        return self::throttleKeyFor((string) $this->string('email'), (string) $this->ip());
    }

    /**
     * Shared so the `admin:unlock` console command can clear exactly the
     * same key this request would set, instead of re-deriving it (and
     * drifting out of sync the moment either side changes).
     */
    public static function throttleKeyFor(string $email, string $ip): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
}
