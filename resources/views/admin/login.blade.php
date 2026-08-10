@extends('layouts.admin')

@section('title', 'Login Admin — Kafeign')

@section('content')
    <div class="flex min-h-[60vh] flex-col items-center justify-center">
        <div class="w-full max-w-sm rounded-2xl border border-kafeign-wood-soft bg-kafeign-cream-soft p-6 shadow-sm dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft">
            <h1 class="font-display text-xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                Login Admin
            </h1>
            <p class="mt-1 text-xs text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                Khusus staf/kasir Kafeign untuk memantau pesanan berjalan.
            </p>

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
                        Email
                    </label>
                    <input type="email" name="email" id="email" required autofocus value="{{ old('email') }}"
                        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
                        Password
                    </label>
                    <input type="password" name="password" id="password" required
                        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-kafeign-brown/80 dark:text-kafeign-cream-soft/80">
                    <input type="checkbox" name="remember" class="rounded border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
                    Ingat saya
                </label>

                <button type="submit"
                    class="w-full rounded-lg bg-kafeign-maroon py-3 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                    Masuk
                </button>
            </form>
        </div>
    </div>
@endsection
