@extends('layouts.app')

@section('title', 'Menu — Meja ' . $table->number . ' — Kafeign')

@section('content')
    <div class="mb-6">
        <p class="text-xs uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">Meja {{ $table->number }}</p>
        <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">Menu</h1>
    </div>

    <nav class="sticky top-14 z-20 -mx-4 mb-8 overflow-x-auto border-b border-kafeign-wood-soft/40 bg-kafeign-cream/95 px-4 py-2 backdrop-blur sm:-mx-6 sm:px-6 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink/95">
        <ul class="flex w-max gap-2 text-sm">
            @foreach ($categories as $category)
                <li>
                    <a href="#kategori-{{ $category->slug }}"
                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border border-kafeign-wood-soft px-3 py-1.5 text-kafeign-brown transition hover:border-kafeign-maroon dark:border-kafeign-ink-border dark:text-kafeign-cream-soft">
                        <x-icon :name="$category->icon" class="h-3.5 w-3.5" />
                        {{ $category->name }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="space-y-10 pb-24">
        @foreach ($categories as $category)
            <section id="kategori-{{ $category->slug }}" class="scroll-mt-32">
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-kafeign-cream-soft text-kafeign-maroon-dark dark:bg-kafeign-ink-soft dark:text-kafeign-amber">
                        <x-icon :name="$category->icon" class="h-4 w-4" />
                    </span>
                    <h2 class="font-display text-xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                        {{ $category->name }}
                    </h2>
                </div>

                @if ($category->menuItems->isEmpty())
                    <p class="text-sm text-kafeign-brown/60 dark:text-kafeign-cream-soft/50">
                        Belum ada menu di kategori ini.
                    </p>
                @else
                    <div class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 px-4 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
                        @foreach ($category->menuItems as $item)
                            <x-menu-item-card :item="$item" :table="$table" />
                        @endforeach
                    </div>
                @endif
            </section>
        @endforeach
    </div>

    <x-order-summary :table="$table" :order="$order" :cart-count="$cartCount" :cart-total="$cartTotal" />
@endsection
