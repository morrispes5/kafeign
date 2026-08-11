@php
    // Shared by both the customer and admin layouts so there is exactly one
    // piece of toast markup in the codebase.
    //
    // Two deliberate constraints shape this file:
    //
    // 1. Server flash messages are rendered here as REAL HTML, not handed to
    //    JavaScript. They therefore still appear with JS disabled or broken —
    //    they simply don't auto-dismiss.
    // 2. The <template> blocks below exist because Tailwind v4 scans source
    //    files for class names. Any class string assembled at runtime in JS
    //    would not appear in any source file, would be purged from the
    //    production build, and the toast would render completely unstyled.
    //    resources/js/toast.js clones these instead of building markup.
    $variants = [
        'success' => ['label' => 'Berhasil', 'classes' => 'border-kafeign-wood-soft bg-kafeign-cream-soft text-kafeign-brown dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft dark:text-kafeign-cream-soft'],
        'error' => ['label' => 'Gagal', 'classes' => 'border-red-300 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-300'],
        'warning' => ['label' => 'Perhatian', 'classes' => 'border-kafeign-amber bg-kafeign-amber-soft text-kafeign-maroon-dark dark:border-kafeign-amber/40 dark:bg-kafeign-amber/10 dark:text-kafeign-amber'],
        'info' => ['label' => 'Info', 'classes' => 'border-kafeign-wood-soft bg-kafeign-cream-soft text-kafeign-brown dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft dark:text-kafeign-cream-soft'],
    ];
@endphp

{{-- Anchored to the TOP: the bottom of the customer screen is occupied by
     the sticky cart/order bar, and a toast must never cover the Kirim
     button. pointer-events-none on the host so it never blocks taps on the
     page behind it; each toast re-enables them for its own dismiss button. --}}
<div data-toast-host aria-live="polite" aria-atomic="false"
    class="pointer-events-none fixed inset-x-0 top-16 z-50 mx-auto flex w-full max-w-md flex-col gap-2 px-4">

    @foreach (array_keys($variants) as $type)
        @if (session($type))
            <div class="pointer-events-auto flex items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-sm {{ $variants[$type]['classes'] }}">
                <div class="min-w-0 flex-1">
                    <p class="font-semibold">{{ $variants[$type]['label'] }}</p>
                    <p class="mt-0.5">{{ session($type) }}</p>
                </div>
                <button type="button" data-toast-dismiss aria-label="Tutup pesan"
                    class="shrink-0 text-current opacity-60 transition hover:opacity-100">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>
        @endif
    @endforeach
</div>

@foreach ($variants as $type => $variant)
    <template data-toast-template="{{ $type }}">
        <div class="pointer-events-auto flex items-start gap-3 rounded-lg border px-4 py-3 text-sm shadow-sm {{ $variant['classes'] }}">
            <div class="min-w-0 flex-1">
                <p class="font-semibold">{{ $variant['label'] }}</p>
                <p data-toast-message class="mt-0.5"></p>
                <button type="button" data-toast-action
                    class="mt-2 hidden rounded-md border border-current px-2.5 py-1 text-xs font-medium transition hover:opacity-80"></button>
            </div>
            <button type="button" data-toast-dismiss aria-label="Tutup pesan"
                class="shrink-0 text-current opacity-60 transition hover:opacity-100">
                <x-icon name="close" class="h-4 w-4" />
            </button>
        </div>
    </template>
@endforeach
