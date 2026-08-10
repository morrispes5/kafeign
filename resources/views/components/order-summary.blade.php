@props(['table', 'order' => null])

@php
    $itemCount = $order?->orderItems->sum('quantity') ?? 0;
    $total = $order?->total ?? 0;
@endphp

{{--
    Sticky bar showing the running tab. Starts translated off-screen when
    there's nothing on the tab yet, and app.js slides it into view (and
    updates the numbers) the moment the first item is added — no reload.
--}}
<div data-order-summary
    class="fixed inset-x-0 bottom-0 z-40 transform border-t border-kafeign-wood-soft bg-kafeign-cream/95 backdrop-blur transition-transform duration-200 ease-out dark:border-kafeign-ink-border dark:bg-kafeign-ink/95 {{ $itemCount > 0 ? 'translate-y-0' : 'translate-y-full' }}">
    <a href="{{ route('table.order', $table) }}" class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3 sm:px-6">
        <span class="text-sm text-kafeign-brown dark:text-kafeign-cream-soft">
            <span data-order-summary-count>{{ $itemCount }}</span> item ·
            <span data-order-summary-total>Rp {{ number_format($total, 0, ',', '.') }}</span>
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-kafeign-maroon px-4 py-2 text-sm font-medium text-kafeign-cream dark:bg-kafeign-amber dark:text-kafeign-ink">
            Lihat Pesanan
            <x-icon name="chevron-right" class="h-3.5 w-3.5" />
        </span>
    </a>
</div>
