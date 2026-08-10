// Ported from the old legacy-static-site/js/main.js (dark-mode toggle +
// hamburger sidebar), rebuilt without emoji: the theme button swaps between
// two inline SVG icons via Tailwind's `dark:` variant instead of swapping
// emoji text, and the "dark" class now goes on <html> (Tailwind v4's
// class-based dark mode convention) instead of a `dark-mode` class on
// <body>. The actual class is applied as early as possible by an inline
// script in the <head> (see layouts/app.blade.php) to avoid a flash of the
// wrong theme; this file only wires up the click handlers.

document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;

    const themeToggle = document.querySelector('[data-theme-toggle]');
    themeToggle?.addEventListener('click', () => {
        const isDark = html.classList.toggle('dark');
        localStorage.setItem('kafeign-theme', isDark ? 'dark' : 'light');
    });

    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openSidebar = () => {
        sidebar?.classList.remove('translate-x-full');
        backdrop?.classList.remove('hidden');
    };
    const closeSidebar = () => {
        sidebar?.classList.add('translate-x-full');
        backdrop?.classList.add('hidden');
    };

    document.querySelector('[data-sidebar-open]')?.addEventListener('click', openSidebar);
    document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);
});
