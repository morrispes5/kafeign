@extends('layouts.app')

@section('title', 'Kafeign — Pilih Nomor Meja')

@section('content')
    <div class="mx-auto max-w-xl text-center">
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-kafeign-wood dark:text-kafeign-wood-soft">
            Selamat Datang di
        </p>
        <h1 class="mt-1 font-display text-4xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
            Kafeign
        </h1>
        <p class="mt-3 text-sm text-kafeign-brown/80 dark:text-kafeign-cream-soft/80">
            Pilih nomor meja tempat kamu duduk untuk mulai melihat menu dan memesan.
        </p>
    </div>

    <div class="mt-8 grid grid-cols-6 gap-2 sm:grid-cols-9">
        @for ($number = 1; $number <= 36; $number++)
            <a href="{{ route('table.show', ['table' => $number]) }}"
                class="flex aspect-square items-center justify-center rounded-xl border border-kafeign-wood-soft bg-kafeign-cream-soft font-display text-lg font-semibold text-kafeign-maroon-dark transition hover:-translate-y-0.5 hover:border-kafeign-maroon hover:shadow-md dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft dark:text-kafeign-amber">
                {{ $number }}
            </a>
        @endfor
    </div>

    <div class="mx-auto mt-10 max-w-sm">
        <p class="text-center text-xs uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
            atau masukkan nomor meja
        </p>

        <form method="POST" action="{{ route('table.enter') }}" class="mt-3 flex gap-2">
            @csrf
            <label for="number" class="sr-only">Nomor meja</label>
            <input type="number" name="number" id="number" min="1" max="36" inputmode="numeric"
                value="{{ old('number') }}" placeholder="Contoh: 7"
                class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-center text-kafeign-brown placeholder:text-kafeign-brown/40 focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
            <button type="submit"
                class="shrink-0 rounded-lg bg-kafeign-maroon px-5 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                Masuk
            </button>
        </form>

        @error('number')
            <p class="mt-2 text-center text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
@endsection
