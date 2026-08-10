@props(['variant' => 'default'])

@php
    $variantClasses = match ($variant) {
        'new' => 'bg-kafeign-amber-soft text-kafeign-maroon-dark dark:bg-kafeign-amber/20 dark:text-kafeign-amber',
        'vdt' => 'bg-kafeign-wood-soft/60 text-kafeign-maroon-dark dark:bg-kafeign-ink-border dark:text-kafeign-cream-soft',
        default => 'bg-kafeign-cream-soft text-kafeign-brown dark:bg-kafeign-ink dark:text-kafeign-cream-soft',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {$variantClasses}"]) }}>
    {{ $slot }}
</span>
