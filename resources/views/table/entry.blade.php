@extends('layouts.app')

@section('title', 'Kafeign — Selamat Datang')

@section('content')
    <div class="flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center py-10 text-center">

        <p class="text-xs font-medium uppercase tracking-[0.3em] text-kafeign-wood dark:text-kafeign-wood-soft">
            Selamat Datang di
        </p>
        <h1 class="mt-2 font-display text-5xl font-semibold text-kafeign-maroon-dark sm:text-6xl dark:text-kafeign-amber">
            Kafeign
        </h1>
        <p class="mx-auto mt-4 max-w-sm text-sm text-kafeign-brown/80 dark:text-kafeign-cream-soft/80">
            Nikmati kelezatan kopi pilihan dari petani terbaik.
        </p>

        <div class="my-8 flex items-center gap-3 text-kafeign-wood dark:text-kafeign-wood-soft">
            <span class="h-px w-10 bg-kafeign-wood-soft dark:bg-kafeign-ink-border"></span>
            <x-icon name="mug" class="h-5 w-5" />
            <span class="h-px w-10 bg-kafeign-wood-soft dark:bg-kafeign-ink-border"></span>
        </div>

        <div class="w-full max-w-sm rounded-2xl border border-kafeign-wood-soft bg-kafeign-cream-soft p-6 shadow-sm dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft">
            <h2 class="font-display text-lg font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                Mulai Pesan
            </h2>
            <p class="mt-1 text-xs text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                Pilih nomor meja sesuai tempat kamu duduk.
            </p>

            <form method="POST" action="{{ route('table.enter') }}" class="mt-5 text-left">
                @csrf
                <label for="number" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
                    Nomor Meja
                </label>

                <div class="relative">
                    <select name="number" id="number" required
                        class="w-full appearance-none rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-3 pr-10 text-center font-display text-base font-medium text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
                        <option value="" disabled {{ old('number') ? '' : 'selected' }}>Pilih nomor meja</option>
                        @for ($number = 1; $number <= 36; $number++)
                            <option value="{{ $number }}" @selected((int) old('number') === $number)>
                                Meja {{ $number }}
                            </option>
                        @endfor
                    </select>
                    <x-icon name="chevron-down" class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-kafeign-wood dark:text-kafeign-wood-soft" />
                </div>

                @error('number')
                    <p class="mt-2 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                @enderror

                <button type="submit"
                    class="mt-4 w-full rounded-lg bg-kafeign-maroon py-3 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                    Lihat Menu
                </button>
            </form>
        </div>

        <p class="mt-6 text-xs text-kafeign-brown/50 dark:text-kafeign-cream-soft/50">
            Nomor meja bisa dilihat pada label di meja tempat kamu duduk.
        </p>
    </div>
@endsection
