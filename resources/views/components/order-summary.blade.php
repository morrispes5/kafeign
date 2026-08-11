@props(['table', 'cartCount' => 0, 'cartTotal' => 0, 'order' => null])

@php
    $billCount = $order?->orderItems->sum('quantity') ?? 0;
    $billTotal = $order?->total ?? 0;
@endphp

{{--
    Two visually distinct bars, never both at once.

    The distinction is load-bearing, not decoration. A cart is private to
    this phone and the cafe cannot see it; a bill has already reached the
    cashier. If a customer confuses the two they walk to the counter
    expecting food nobody has been told to make. So the cart bar is amber
    with a dashed edge and says "Keranjang", while the bill bar is solid
    maroon and says "Tagihan".
--}}
@if ($cartCount > 0)
    <div data-order-summary
        class="fixed inset-x-0 bottom-0 z-40 border-t-2 border-dashed border-kafeign-amber bg-kafeign-amber-soft/95 backdrop-blur dark:border-kafeign-amber/60 dark:bg-kafeign-ink-soft/95">
        <a href="{{ route('table.cart', $table) }}" class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <span class="min-w-0 text-sm text-kafeign-maroon-dark dark:text-kafeign-amber">
                <span class="block text-[11px] font-semibold uppercase tracking-wide opacity-70">Keranjang · belum dikirim</span>
                <span data-order-summary-count>{{ $cartCount }}</span> item ·
                <span data-order-summary-total>Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
            </span>
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-kafeign-maroon px-4 py-2 text-sm font-medium text-kafeign-cream dark:bg-kafeign-amber dark:text-kafeign-ink">
                Lihat Keranjang
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            </span>
        </a>
    </div>
@elseif ($billCount > 0)
    <div data-order-summary
        class="fixed inset-x-0 bottom-0 z-40 border-t border-kafeign-wood-soft bg-kafeign-cream/95 backdrop-blur dark:border-kafeign-ink-border dark:bg-kafeign-ink/95">
        <a href="{{ route('table.order', $table) }}" class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <span class="min-w-0 text-sm text-kafeign-brown dark:text-kafeign-cream-soft">
                <span class="block text-[11px] font-semibold uppercase tracking-wide opacity-70">Tagihan meja · sudah di kasir</span>
                {{ $billCount }} item · Rp {{ number_format($billTotal, 0, ',', '.') }}
            </span>
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-kafeign-maroon px-4 py-2 text-sm font-medium text-kafeign-cream dark:bg-kafeign-amber dark:text-kafeign-ink">
                Lihat Pesanan
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            </span>
        </a>
    </div>
@else
    {{-- Rendered empty and hidden so app.js has a node to reveal after the
         first successful add, without needing to build markup. --}}
    <div data-order-summary
        class="fixed inset-x-0 bottom-0 z-40 translate-y-full border-t-2 border-dashed border-kafeign-amber bg-kafeign-amber-soft/95 backdrop-blur transition-transform duration-200 ease-out dark:border-kafeign-amber/60 dark:bg-kafeign-ink-soft/95">
        <a href="{{ route('table.cart', $table) }}" class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-3 sm:px-6">
            <span class="min-w-0 text-sm text-kafeign-maroon-dark dark:text-kafeign-amber">
                <span class="block text-[11px] font-semibold uppercase tracking-wide opacity-70">Keranjang · belum dikirim</span>
                <span data-order-summary-count>0</span> item ·
                <span data-order-summary-total>Rp 0</span>
            </span>
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-kafeign-maroon px-4 py-2 text-sm font-medium text-kafeign-cream dark:bg-kafeign-amber dark:text-kafeign-ink">
                Lihat Keranjang
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            </span>
        </a>
    </div>
@endif
