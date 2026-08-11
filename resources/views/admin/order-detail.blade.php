@extends('layouts.admin')

@section('title', 'Meja ' . $order->table->number . ' — Admin — Kafeign')

@section('content')
    <a href="{{ $order->status === \App\Enums\OrderStatus::Ongoing ? route('admin.dashboard') : route('admin.orders.history') }}"
        class="mb-4 inline-flex items-center gap-1 text-sm text-kafeign-brown/70 transition hover:text-kafeign-maroon-dark dark:text-kafeign-cream-soft/70 dark:hover:text-kafeign-amber">
        &larr; {{ $order->status === \App\Enums\OrderStatus::Ongoing ? 'Kembali ke Dashboard' : 'Kembali ke Riwayat' }}
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
            <form method="POST" action="{{ route('admin.orders.clear', $order) }}" class="flex-1 rounded-xl border border-kafeign-wood-soft/50 p-4 dark:border-kafeign-ink-border/60"
                data-confirm="Konfirmasi: pelanggan meja {{ $order->table->number }} sudah membayar Rp {{ number_format($order->total, 0, ',', '.') }}?">
                @csrf

                <div class="mb-3">
                    <p class="mb-1.5 text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
                        Metode Pembayaran
                    </p>
                    <div class="grid grid-cols-2 gap-2" data-payment-method-group>
                        @foreach (\App\Enums\PaymentMethod::cases() as $method)
                            <label class="flex items-center gap-2 rounded-lg border border-kafeign-wood-soft px-3 py-2 text-sm text-kafeign-brown transition has-[:checked]:border-kafeign-maroon has-[:checked]:bg-kafeign-amber-soft/50 dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:has-[:checked]:border-kafeign-amber">
                                <input type="radio" name="payment_method" value="{{ $method->value }}"
                                    data-requires-cash="{{ $method->requiresCashReceived() ? '1' : '0' }}"
                                    @checked(old('payment_method', 'cash') === $method->value)
                                    class="border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
                                {{ $method->label() }}
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method')
                        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4" data-cash-fields>
                    <label for="cash_received" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
                        Uang Diterima (Rp)
                    </label>
                    <input type="number" name="cash_received" id="cash_received" min="0" step="500"
                        value="{{ old('cash_received') }}" data-cash-received-input data-order-total="{{ $order->total }}"
                        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
                    @error('cash_received')
                        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 hidden text-sm text-kafeign-brown/80 dark:text-kafeign-cream-soft/80" data-change-preview>
                        Kembalian: <span class="font-semibold" data-change-preview-amount>Rp 0</span>
                    </p>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-kafeign-maroon py-3 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                    Tandai Sudah Dibayar &amp; Bersihkan Meja
                </button>
            </form>

            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" class="rounded-xl border border-kafeign-wood-soft/50 p-4 dark:border-kafeign-ink-border/60"
                data-confirm="Batalkan pesanan meja {{ $order->table->number }} tanpa pembayaran?">
                @csrf
                {{-- Only the cashier knows whether the food was already
                     made. Default on, because the common case is a customer
                     who left before anything was prepared. --}}
                <label class="mb-2 flex items-center gap-2 text-xs text-kafeign-brown/80 dark:text-kafeign-cream-soft/80">
                    <input type="checkbox" name="restore_stock" value="1" checked
                        class="rounded border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
                    Kembalikan stok ke menu
                </label>
                <button type="submit"
                    class="w-full rounded-lg border border-kafeign-wood-soft px-5 py-3 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                    Batalkan Pesanan
                </button>
            </form>
        </div>
    @elseif ($order->status === \App\Enums\OrderStatus::Paid)
        <div class="mt-4 rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 p-4 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
            <dl class="grid grid-cols-2 gap-y-2 text-sm">
                <dt class="text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">No. Struk</dt>
                <dd class="text-right font-medium text-kafeign-brown dark:text-kafeign-cream-soft">{{ $order->receipt_number ?? '—' }}</dd>

                <dt class="text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">Metode Bayar</dt>
                <dd class="text-right font-medium text-kafeign-brown dark:text-kafeign-cream-soft">{{ $order->payment_method?->label() ?? '—' }}</dd>

                @if ($order->payment_method?->requiresCashReceived())
                    <dt class="text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">Uang Diterima</dt>
                    <dd class="text-right font-medium text-kafeign-brown dark:text-kafeign-cream-soft">Rp {{ number_format($order->cash_received, 0, ',', '.') }}</dd>

                    <dt class="text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">Kembalian</dt>
                    <dd class="text-right font-medium text-kafeign-brown dark:text-kafeign-cream-soft">Rp {{ number_format($order->change_due, 0, ',', '.') }}</dd>
                @endif
            </dl>

            <a href="{{ route('admin.orders.receipt', $order) }}"
                class="mt-4 block w-full rounded-lg bg-kafeign-maroon py-3 text-center text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                Cetak Struk
            </a>

            <p class="mt-3 text-center text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
                Ditutup pada {{ $order->closed_at->format('d M Y, H:i') }}
            </p>
        </div>
    @elseif ($order->closed_at)
        <p class="mt-4 text-center text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
            Ditutup pada {{ $order->closed_at->format('d M Y, H:i') }}
        </p>
    @endif
@endsection
