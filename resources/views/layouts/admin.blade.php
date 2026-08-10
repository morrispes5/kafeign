<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — Kafeign')</title>

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
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3 sm:px-6">
            <div class="flex items-center gap-3">
                <span class="font-display text-xl font-semibold tracking-wide text-kafeign-maroon-dark dark:text-kafeign-amber">
                    KAFEIGN
                </span>
                <span class="rounded-full border border-kafeign-wood-soft px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-kafeign-wood dark:border-kafeign-ink-border dark:text-kafeign-wood-soft">
                    Admin
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" data-theme-toggle aria-label="Ganti tampilan terang/gelap"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-kafeign-wood-soft text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                    <x-icon name="sun" class="hidden h-4 w-4 dark:block" />
                    <x-icon name="moon" class="block h-4 w-4 dark:hidden" />
                </button>

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.menu-items.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                        Menu
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                        Kategori
                    </a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-kafeign-wood-soft px-3 py-2 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
                            Keluar
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-kafeign-wood-soft bg-kafeign-cream-soft px-4 py-3 text-sm text-kafeign-brown dark:border-kafeign-ink-border dark:bg-kafeign-ink-soft dark:text-kafeign-cream-soft">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
