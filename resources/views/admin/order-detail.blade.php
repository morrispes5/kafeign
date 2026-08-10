@extends('layouts.admin')

@section('title', 'Meja ' . $order->table->number . ' — Admin — Kafeign')

@section('content')
    <a href="{{ route('admin.dashboard') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-kafeign-brown/70 transition hover:text-kafeign-maroon-dark dark:text-kafeign-cream-soft/70 dark:hover:text-kafeign-amber">
        &larr; Kembali ke Dashboard
    </a>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
                Meja {{ $order->table->number }} · dibuka {{ $order->opened_at->format('d M Y, H:i') }}
            </p>
            <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                Detail Pesanan
            </h1>
        </div>

        <span @class([
            'rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide',
            'bg-kafeign-amber-soft text-kafeign-maroon-dark dark:bg-kafeign-amber/20 dark:text-kafeign-amber' => $order->status === \App\Enums\OrderStatus::Ongoing,
            'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $order->status === \App\Enums\OrderStatus::Paid,
            'bg-kafeign-wood-soft/50 text-kafeign-brown dark:bg-kafeign-ink-border dark:text-kafeign-cream-soft' => $order->status === \App\Enums\OrderStatus::Cancelled,
        ])>
            {{ $order->status->label() }}
        </span>
    </div>

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

    @if ($order->status === \App\Enums\OrderStatus::Ongoing)
        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <form method="POST" action="{{ route('admin.orders.clear', $order) }}" class="flex-1"
                onsubmit="return confirm('Konfirmasi: pelanggan meja {{ $order->table->number }} sudah membayar Rp {{ number_format($order->total, 0, ',', '.') }}?');">
                @csrf
                <button type="submit"
                    class="w-full rounded-lg bg-kafeign-maroon py-3 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                    Tandai Sudah Dibayar &amp; Bersihkan Meja
                </button>
            </form>

            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
                onsubmit="return confirm('Batalkan pesanan meja {{ $order->table->number }} tanpa pembayaran?');">
                @csrf
                <button type="submit"
                    class="w-full rounded-lg border border-kafeign-wood-soft px-5 py-3 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                    Batalkan Pesanan
                </button>
            </form>
        </div>
    @elseif ($order->closed_at)
        <p class="mt-4 text-center text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
            Ditutup pada {{ $order->closed_at->format('d M Y, H:i') }}
        </p>
    @endif
@endsection
