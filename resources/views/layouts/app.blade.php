<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kafeign')</title>

    {{-- Applied before Vite's CSS/JS load so there's no flash of the wrong
         theme on page load. Actual toggle handling lives in resources/js/app.js. --}}
    <script>
        (function () {
            var saved = localStorage.getItem('kafeign-theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-kafeign-cream text-kafeign-brown antialiased transition-colors dark:bg-kafeign-ink dark:text-kafeign-cream-soft">

    <header class="sticky top-0 z-30 border-b border-kafeign-wood-soft/60 bg-kafeign-cream/95 backdrop-blur dark:border-kafeign-ink-border dark:bg-kafeign-ink/95">
        <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ route('table.entry') }}" class="font-display text-xl font-semibold tracking-wide text-kafeign-maroon-dark dark:text-kafeign-amber">
                KAFEIGN
            </a>

            <div class="flex items-center gap-2">
                @isset($table)
                    <span class="hidden items-center rounded-full border border-kafeign-wood-soft px-3 py-1 text-xs font-medium uppercase tracking-wide text-kafeign-brown sm:inline-flex dark:border-kafeign-ink-border dark:text-kafeign-cream-soft">
                        Meja {{ $table->number }}
                    </span>
                @endisset

                <button type="button" data-theme-toggle aria-label="Ganti tampilan terang/gelap"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-kafeign-wood-soft text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                    <x-icon name="sun" class="hidden h-4 w-4 dark:block" />
                    <x-icon name="moon" class="block h-4 w-4 dark:hidden" />
                </button>

                <button type="button" data-sidebar-open aria-label="Buka menu navigasi"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-kafeign-wood-soft text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                    <x-icon name="menu" class="h-4 w-4" />
                </button>
            </div>
        </div>
    </header>

    <div data-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-black/40"></div>

    <aside data-sidebar
        class="fixed inset-y-0 right-0 z-50 w-72 translate-x-full transform border-l border-kafeign-wood-soft bg-kafeign-cream p-5 shadow-xl transition-transform duration-200 ease-out dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft">
        <div class="flex items-center justify-between">
            <span class="font-display text-lg font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">KAFEIGN</span>
            <button type="button" data-sidebar-close aria-label="Tutup menu navigasi"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink">
                <x-icon name="close" class="h-4 w-4" />
            </button>
        </div>

        @isset($table)
            <div class="mt-6 rounded-xl border border-kafeign-wood-soft bg-kafeign-cream-soft p-4 dark:border-kafeign-ink-border dark:bg-kafeign-ink">
                <p class="text-xs uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">Sedang duduk di</p>
                <p class="font-display text-2xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">Meja {{ $table->number }}</p>
            </div>
        @endisset

        <nav class="mt-6 flex flex-col gap-1 text-sm">
            @isset($table)
                <a href="{{ route('table.menu', $table) }}" class="rounded-lg px-3 py-2 transition hover:bg-kafeign-cream-soft dark:hover:bg-kafeign-ink">Menu</a>
            @endisset
            <a href="{{ route('table.entry') }}" class="rounded-lg px-3 py-2 transition hover:bg-kafeign-cream-soft dark:hover:bg-kafeign-ink">Ganti Meja</a>
        </nav>
    </aside>

    <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
        @yield('content')
    </main>

</body>
</html>
