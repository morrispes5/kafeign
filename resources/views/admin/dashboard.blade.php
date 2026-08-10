@extends('layouts.admin')

@section('title', 'Dashboard Admin — Kafeign')

@section('content')
    <div class="mb-6 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                Meja Aktif
            </h1>
            <p class="mt-1 text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                {{ $activeOrders->count() }} dari {{ $totalTables }} meja sedang berjalan.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
            class="rounded-lg border border-kafeign-wood-soft px-3 py-2 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
            Muat Ulang
        </a>
    </div>

    @if ($activeOrders->isEmpty())
        <div class="rounded-xl border border-dashed border-kafeign-wood-soft p-10 text-center dark:border-kafeign-ink-border">
            <p class="text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                Belum ada meja yang sedang aktif saat ini.
            </p>
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($activeOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}"
                    class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 p-4 transition hover:border-kafeign-maroon hover:shadow-md dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
                    <div class="flex items-center justify-between">
                        <span class="font-display text-2xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                            Meja {{ $order->table->number }}
                        </span>
                        <span class="rounded-full bg-kafeign-amber-soft px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-kafeign-maroon-dark dark:bg-kafeign-amber/20 dark:text-kafeign-amber">
                            Berjalan
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-kafeign-brown/80 dark:text-kafeign-cream-soft/80">
                        {{ $order->orderItems->sum('quantity') }} item · sejak {{ $order->opened_at->format('H:i') }}
                    </p>

                    <p class="mt-3 font-display text-xl font-semibold text-kafeign-brown dark:text-kafeign-cream-soft">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
