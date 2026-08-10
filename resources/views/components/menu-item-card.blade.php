@props(['item'])

<div class="flex items-center justify-between gap-4 border-b border-kafeign-wood-soft/40 py-3 last:border-b-0 dark:border-kafeign-ink-border/60">
    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
            <p class="font-medium text-kafeign-brown dark:text-kafeign-cream-soft">{{ $item->name }}</p>
            @if ($item->is_new)
                <x-badge-pill variant="new">Baru</x-badge-pill>
            @endif
            @if ($item->is_vdt)
                <x-badge-pill variant="vdt">VDT</x-badge-pill>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <span class="font-display text-sm font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
            Rp {{ number_format($item->price, 0, ',', '.') }}
        </span>

        {{-- Inert in Phase 1 — wired up to POST /table/{table}/order-items in Phase 2. --}}
        <button type="button" disabled aria-label="Tambah {{ $item->name }} (segera hadir)"
            title="Pemesanan aktif mulai Phase 2"
            class="inline-flex h-8 w-8 shrink-0 cursor-not-allowed items-center justify-center rounded-full border border-kafeign-wood-soft/60 text-kafeign-brown/30 dark:border-kafeign-ink-border dark:text-kafeign-cream-soft/25">
            <x-icon name="plus" class="h-4 w-4" />
        </button>
    </div>
</div>
