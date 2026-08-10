@props(['name'])

@php
    // Hand-drawn, line-style icons (no emoji, no stock icon set, no
    // photos) — one per menu category plus a handful of small UI icons
    // for the header/sidebar. All share the same stroke style below and
    // inherit color via `currentColor`, so `text-*` classes control them.
    $paths = match ($name) {
        // --- Category icons -------------------------------------------------
        'mug' => '
            <path d="M5.5 8.5h10a1 1 0 0 1 1 1V16a3.5 3.5 0 0 1-3.5 3.5h-5A3.5 3.5 0 0 1 4.5 16V9.5a1 1 0 0 1 1-1z" />
            <path d="M16.5 10.5h1.25a2.25 2.25 0 0 1 0 4.5H16.5" />
            <path d="M8 5.5c.5-1 .5-1.5 0-2.5M11.5 5.5c.5-1 .5-1.5 0-2.5" />
        ',
        'coffee-cup' => '
            <path d="M6.5 8h11l-1.1 10.8a2 2 0 0 1-2 1.7h-4.8a2 2 0 0 1-2-1.7L6.5 8z" />
            <path d="M5.5 8h13" />
            <path d="M9.5 5c.4-.8.4-1.2 0-2M13.5 5c.4-.8.4-1.2 0-2" />
        ',
        'glass' => '
            <path d="M8 4h8l-1.2 15a1 1 0 0 1-1 .9h-3.6a1 1 0 0 1-1-.9L8 4z" />
            <path d="M7.5 4h9" />
            <path d="M15 2.5 11 10" />
        ',
        'leaf' => '
            <path d="M12 3C7 5 4 9 4 13a8 8 0 0 0 8 8c5-2 8-6 8-10 0-4-3-8-8-8z" />
            <path d="M12 21V7" />
        ',
        'drip' => '
            <path d="M6.5 6h11l-4 8.5h-3l-4-8.5z" />
            <path d="M9.5 17.5h5v1.5a2.5 2.5 0 0 1-2.5 2.5 2.5 2.5 0 0 1-2.5-2.5v-1.5z" />
            <path d="M12 2.5v2" />
        ',
        'mocktail-glass' => '
            <path d="M5 5h14L12 14 5 5z" />
            <path d="M12 14v6" />
            <path d="M8.5 20h7" />
        ',
        'tall-glass' => '
            <path d="M7.5 4h9l-1 15.2a1 1 0 0 1-1 .8h-5a1 1 0 0 1-1-.8L7.5 4z" />
            <path d="M9 9.5h6M9.5 13h5" />
        ',
        'steak' => '
            <path d="M6 2v6a1.5 1.5 0 0 0 3 0V2" />
            <path d="M7.5 8v13" />
            <path d="M16.5 2c-2 0-3 2-3 5s1 4 3 4v11" />
        ',
        'snack-plate' => '
            <circle cx="12" cy="12" r="8" />
            <circle cx="12" cy="12" r="4.5" />
        ',

        // --- UI icons ---------------------------------------------------
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16" />',
        'close' => '<path d="M6 6l12 12M18 6L6 18" />',
        'sun' => '
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2M12 20v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M2 12h2M20 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
        ',
        'moon' => '<path d="M20 14.5A8.5 8.5 0 1 1 9.5 4a7 7 0 0 0 10.5 10.5z" />',
        'chevron-right' => '<path d="M9 5l7 7-7 7" />',
        'chevron-down' => '<path d="M5 9l7 7 7-7" />',
        'plus' => '<path d="M12 5v14M5 12h14" />',
        'check' => '<path d="M5 13l4 4L19 7" />',

        default => '',
    };
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
    {{ $attributes->merge(['class' => 'w-5 h-5']) }}>
    {!! $paths !!}
</svg>
