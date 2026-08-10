@extends('layouts.app')

@section('title', 'Pesanan — Meja ' . $table->number . ' — Kafeign')

@section('content')
    <div class="mb-6">
        <p class="text-xs uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">Meja {{ $table->number }}</p>
        <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">Pesanan Kamu</h1>
    </div>

    @if (! $order || $order->orderItems->isEmpty())
        <div class="rounded-xl border border-dashed border-kafeign-wood-soft p-8 text-center dark:border-kafeign-ink-border">
            <p class="text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                Belum ada pesanan yang berjalan di meja ini.
            </p>
            <a href="{{ route('table.menu', $table) }}"
                class="mt-4 inline-flex rounded-lg bg-kafeign-maroon px-5 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                Lihat Menu
            </a>
        </div>
    @else
        <div class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 px-4 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
            @foreach ($order->orderItems as $item)
                <div class="flex items-center justify-between gap-4 border-b border-kafeign-wood-soft/40 py-3 last:border-b-0 dark:border-kafeign-ink-border/60">
                    <div>
                        <p class="font-medium text-kafeign-brown dark:text-kafeign-cream-soft">{{ $item->item_name }}</p>
                        <p class="text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
                            {{ $item->quantity }} &times; Rp {{ number_format($item->item_price, 0, ',', '.') }}
                        </p>
                    </div>
                    <span class="shrink-0 font-display text-sm font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between rounded-xl bg-kafeign-maroon px-5 py-4 text-kafeign-cream dark:bg-kafeign-amber dark:text-kafeign-ink">
            <span class="text-sm font-medium uppercase tracking-wide">Total</span>
            <span class="font-display text-xl font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>

        <p class="mt-4 text-center text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
            Pesanan ini akan terus berjalan sampai kamu membayar di kasir.
        </p>

        <a href="{{ route('table.menu', $table) }}"
            class="mt-6 flex items-center justify-center gap-1.5 rounded-lg border border-kafeign-wood-soft py-3 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Pesanan
        </a>
    @endif
@endsection
