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

    // --- Add-to-order (Phase 2) ------------------------------------------
    // Menu page "+" buttons POST to /table/{table}/order-items and get
    // back JSON, so the sticky order-summary bar (components/order-
    // summary.blade.php) can update in place without a page reload.
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const summaryBar = document.querySelector('[data-order-summary]');
    const summaryCount = document.querySelector('[data-order-summary-count]');
    const summaryTotal = document.querySelector('[data-order-summary-total]');

    document.querySelectorAll('[data-add-item]').forEach((button) => {
        const idleIcon = button.querySelector('[data-icon-idle]');
        const loadingIcon = button.querySelector('[data-icon-loading]');
        const successIcon = button.querySelector('[data-icon-success]');

        const showState = (state) => {
            idleIcon?.classList.toggle('hidden', state !== 'idle');
            loadingIcon?.classList.toggle('hidden', state !== 'loading');
            successIcon?.classList.toggle('hidden', state !== 'success');
        };

        button.addEventListener('click', async () => {
            const url = button.dataset.orderItemsUrl;
            if (!url || button.disabled) {
                return;
            }

            button.disabled = true;
            showState('loading');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken ?? '',
                    },
                    body: JSON.stringify({
                        menu_item_id: button.dataset.menuItemId,
                        quantity: 1,
                    }),
                });

                if (!response.ok) {
                    throw new Error(`Add to order failed with status ${response.status}`);
                }

                const data = await response.json();

                if (summaryCount) summaryCount.textContent = data.order.item_count;
                if (summaryTotal) summaryTotal.textContent = data.order.total_formatted;
                summaryBar?.classList.remove('translate-y-full');
                summaryBar?.classList.add('translate-y-0');

                showState('success');
                setTimeout(() => showState('idle'), 700);
            } catch (error) {
                console.error('Gagal menambah pesanan:', error);
                showState('idle');
                button.classList.add('border-red-500');
                setTimeout(() => button.classList.remove('border-red-500'), 1200);
            } finally {
                button.disabled = false;
            }
        });
    });
});
