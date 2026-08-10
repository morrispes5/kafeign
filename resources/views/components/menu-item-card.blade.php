@props(['item', 'table'])

<div class="flex items-center justify-between gap-4 border-b border-kafeign-wood-soft/40 py-4 last:border-b-0 dark:border-kafeign-ink-border/60">
    <div class="flex min-w-0 items-center gap-4">
        {{-- Only renders once an admin uploads a photo — most items stay
             text-only. Sized to actually read as food photography (not an
             icon) once one is uploaded; App\Support\MenuItemImage resizes
             whatever the admin uploads down to a consistent quality/size
             before it ever reaches this <img>. --}}
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="" loading="lazy"
                class="h-24 w-24 shrink-0 rounded-xl border border-kafeign-wood-soft/40 object-cover dark:border-kafeign-ink-border/60">
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
            {{-- Three pre-rendered states swapped by app.js, so a click
                 never has to wait for the fetch before showing feedback. --}}
            <span data-icon-idle>
                <x-icon name="plus" class="h-4 w-4" />
            </span>
            <span data-icon-loading class="hidden">
                <span class="block h-3.5 w-3.5 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
            </span>
            <span data-icon-success class="hidden">
                <x-icon name="check" class="h-4 w-4" />
            </span>
        </button>
    </div>
</div>
