@extends('layouts.app')

@section('title', 'Halaman Tidak Ditemukan — Kafeign')

@section('content')
    <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
        <p class="font-display text-7xl font-semibold text-kafeign-wood-soft dark:text-kafeign-ink-border">
            404
        </p>
        <h1 class="mt-4 font-display text-2xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
            Halaman Tidak Ditemukan
        </h1>
        <p class="mx-auto mt-3 max-w-sm text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
            Mungkin nomor mejanya salah, atau tautan yang kamu buka sudah
            tidak berlaku. Coba masuk lagi dari halaman utama.
        </p>

        <a href="{{ route('table.entry') }}"
            class="mt-6 inline-flex rounded-lg bg-kafeign-maroon px-5 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
            Kembali ke Beranda
        </a>
    </div>
@endsection
