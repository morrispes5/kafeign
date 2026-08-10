@extends('layouts.admin')

@section('title', 'Menu — Admin — Kafeign')

@section('content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                Kelola Menu
            </h1>
            <p class="mt-1 text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                {{ $categories->sum(fn ($c) => $c->menuItems->count()) }} item di {{ $categories->count() }} kategori.
            </p>
        </div>
        <a href="{{ route('admin.menu-items.create') }}"
            class="rounded-lg bg-kafeign-maroon px-4 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
            + Tambah Item
        </a>
    </div>

    <div class="space-y-8">
        @foreach ($categories as $category)
            <section>
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-kafeign-cream-soft text-kafeign-maroon-dark dark:bg-kafeign-ink-soft dark:text-kafeign-amber">
                        <x-icon :name="$category->icon" class="h-4 w-4" />
                    </span>
                    <h2 class="font-display text-xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                        {{ $category->name }}
                    </h2>
                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-xs text-kafeign-brown/50 underline decoration-dotted hover:text-kafeign-maroon-dark dark:text-kafeign-cream-soft/50 dark:hover:text-kafeign-amber">
                        ubah kategori
                    </a>
                </div>

                @if ($category->menuItems->isEmpty())
                    <p class="text-sm text-kafeign-brown/60 dark:text-kafeign-cream-soft/50">Belum ada item di kategori ini.</p>
                @else
                    <div class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 px-4 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
                        @foreach ($category->menuItems as $item)
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-kafeign-wood-soft/40 py-3 last:border-b-0 dark:border-kafeign-ink-border/60">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="" class="h-16 w-16 shrink-0 rounded-lg object-cover">
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                            <p class="font-medium text-kafeign-brown dark:text-kafeign-cream-soft {{ $item->is_available ? '' : 'opacity-50' }}">
                                                {{ $item->name }}
                                            </p>
                                            @if ($item->is_new)
                                                <x-badge-pill variant="new">Baru</x-badge-pill>
                                            @endif
                                            @if ($item->is_vdt)
                                                <x-badge-pill variant="vdt">VDT</x-badge-pill>
                                            @endif
                                            @unless ($item->is_available)
                                                <x-badge-pill>Nonaktif</x-badge-pill>
                                            @endunless
                                        </div>
                                        <p class="text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <form method="POST" action="{{ route('admin.menu-items.toggle-availability', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="rounded-lg border border-kafeign-wood-soft px-3 py-1.5 text-xs font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                                            {{ $item->is_available ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.menu-items.edit', $item) }}"
                                        class="rounded-lg border border-kafeign-wood-soft px-3 py-1.5 text-xs font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                                        Ubah
                                    </a>
                                    <form method="POST" action="{{ route('admin.menu-items.destroy', $item) }}"
                                        data-confirm="Hapus item {{ $item->name }} secara permanen?">
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
                @endif
            </section>
        @endforeach
    </div>
@endsection
