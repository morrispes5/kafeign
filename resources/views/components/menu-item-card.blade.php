@props(['item', 'table'])

<div class="flex items-center justify-between gap-4 border-b border-kafeign-wood-soft/40 py-3 last:border-b-0 dark:border-kafeign-ink-border/60">
    <div class="flex min-w-0 items-center gap-3">
        {{-- Only renders once an admin uploads a photo (Phase 4) — every
             item is text-only today, so this stays invisible for now. --}}
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="" loading="lazy"
                class="h-12 w-12 shrink-0 rounded-lg object-cover">
        @endif

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
    </div>

    <div class="flex shrink-0 items-center gap-3">
        <span class="font-display text-sm font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
            Rp {{ number_format($item->price, 0, ',', '.') }}
        </span>

        <button type="button" data-add-item
            data-order-items-url="{{ route('table.order-items.store', $table) }}"
            data-menu-item-id="{{ $item->id }}"
            aria-label="Tambah {{ $item->name }}"
            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-kafeign-wood-soft text-kafeign-maroon-dark transition hover:border-kafeign-maroon hover:bg-kafeign-maroon hover:text-kafeign-cream disabled:cursor-wait disabled:opacity-60 dark:border-kafeign-ink-border dark:text-kafeign-amber dark:hover:bg-kafeign-amber dark:hover:text-kafeign-ink">
            <x-icon name="plus" class="h-4 w-4" />
        </button>
    </div>
</div>
