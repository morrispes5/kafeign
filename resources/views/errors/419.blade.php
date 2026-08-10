@extends('layouts.app')

@section('title', 'Sesi Berakhir — Kafeign')

@section('content')
    <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
        <p class="font-display text-7xl font-semibold text-kafeign-wood-soft dark:text-kafeign-ink-border">
            419
        </p>
        <h1 class="mt-4 font-display text-2xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
            Sesi Sudah Berakhir
        </h1>
        <p class="mx-auto mt-3 max-w-sm text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
            Halaman ini sudah terbuka terlalu lama. Muat ulang halamannya,
            lalu coba lagi.
        </p>

        <button type="button" onclick="window.location.reload()"
            class="mt-6 inline-flex rounded-lg bg-kafeign-maroon px-5 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
            Muat Ulang Halaman
        </button>
    </div>
@endsection
