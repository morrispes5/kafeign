@extends('layouts.admin')

@section('title', 'Riwayat Pesanan — Admin — Kafeign')

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
            Riwayat Pesanan
        </h1>
        <p class="mt-1 text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
            Pesanan yang sudah lunas atau dibatalkan. Meja yang masih berjalan ada di
            <a href="{{ route('admin.dashboard') }}" class="underline hover:text-kafeign-maroon-dark dark:hover:text-kafeign-amber">Dashboard</a>.
        </p>
    </div>

    @if ($orders->isEmpty())
        <div class="rounded-xl border border-dashed border-kafeign-wood-soft p-10 text-center dark:border-kafeign-ink-border">
            <p class="text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                Belum ada pesanan yang selesai.
            </p>
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($orders as $order)
                <a href="{{ route('admin.orders.show', $order) }}"
                    class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 p-4 transition hover:border-kafeign-maroon hover:shadow-md dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
                    <div class="flex items-center justify-between">
                        <span class="font-display text-xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                            Meja {{ $order->table->number }}
                        </span>
                        <span @class([
                            'rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide',
                            'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' => $order->status === \App\Enums\OrderStatus::Paid,
                            'bg-kafeign-wood-soft/50 text-kafeign-brown dark:bg-kafeign-ink-border dark:text-kafeign-cream-soft' => $order->status === \App\Enums\OrderStatus::Cancelled,
                        ])>
                            {{ $order->status->label() }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-kafeign-brown/80 dark:text-kafeign-cream-soft/80">
                        {{ $order->closed_at?->format('d M Y, H:i') }}
                        @if ($order->receipt_number)
                            · {{ $order->receipt_number }}
                        @endif
                    </p>

                    @if ($order->payment_method)
                        <p class="mt-1 text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
                            {{ $order->payment_method->label() }}
                        </p>
                    @endif

                    <p class="mt-3 font-display text-xl font-semibold text-kafeign-brown dark:text-kafeign-cream-soft">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </p>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
