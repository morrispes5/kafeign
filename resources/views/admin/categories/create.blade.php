@extends('layouts.admin')

@section('title', 'Tambah Kategori — Admin — Kafeign')

@section('content')
    <a href="{{ route('admin.categories.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-kafeign-brown/70 transition hover:text-kafeign-maroon-dark dark:text-kafeign-cream-soft/70 dark:hover:text-kafeign-amber">
        &larr; Kembali ke Kategori
    </a>

    <h1 class="mb-6 font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
        Tambah Kategori
    </h1>

    <form method="POST" action="{{ route('admin.categories.store') }}"
        class="max-w-lg rounded-xl border border-kafeign-wood-soft bg-kafeign-cream-soft p-6 dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft">
        @csrf
        @include('admin.categories._form')

        <button type="submit"
            class="mt-6 w-full rounded-lg bg-kafeign-maroon py-3 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
            Simpan Kategori
        </button>
    </form>
@endsection
