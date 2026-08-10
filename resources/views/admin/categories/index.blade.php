@extends('layouts.admin')

@section('title', 'Kategori — Admin — Kafeign')

@section('content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                Kategori Menu
            </h1>
            <p class="mt-1 text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                {{ $categories->count() }} kategori.
            </p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
            class="rounded-lg bg-kafeign-maroon px-4 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
            + Tambah Kategori
        </a>
    </div>

    <div class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 px-4 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
        @foreach ($categories as $category)
            <div class="flex items-center justify-between gap-4 border-b border-kafeign-wood-soft/40 py-3 last:border-b-0 dark:border-kafeign-ink-border/60">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-kafeign-cream-soft text-kafeign-maroon-dark dark:bg-kafeign-ink-soft dark:text-kafeign-amber">
                        <x-icon :name="$category->icon" class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="font-medium text-kafeign-brown dark:text-kafeign-cream-soft">{{ $category->name }}</p>
                        <p class="text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
                            {{ $category->menu_items_count }} item · urutan #{{ $category->sort_order }}
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <a href="{{ route('admin.categories.edit', $category) }}"
                        class="rounded-lg border border-kafeign-wood-soft px-3 py-1.5 text-xs font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                        Ubah
                    </a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                        data-confirm="Hapus kategori {{ $category->name }}?">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
