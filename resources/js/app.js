// Ported from the old legacy-static-site/js/main.js (dark-mode toggle +
// hamburger sidebar), rebuilt without emoji: the theme button swaps between
// two inline SVG icons via Tailwind's `dark:` variant instead of swapping
// emoji text, and the "dark" class now goes on <html> (Tailwind v4's
// class-based dark mode convention) instead of a `dark-mode` class on
// <body>. The actual class is applied as early as possible by an inline
// script in the <head> (see layouts/app.blade.php) to avoid a flash of the
// wrong theme; this file only wires up the click handlers.

import { initToasts, showToast } from './toast';

/**
 * Every customer-facing fetch() goes through here.
 *
 * Before this existed, app.js checked `response.ok` and threw away
 * everything else — so the server's messages never reached the customer and
 * a failure showed up only as an unexplained red flash. It also assumed the
 * body was always JSON; an HTML error page made .json() throw and buried the
 * real cause.
 *
 * Note this only works because bootstrap/app.php now ORs expectsJson() into
 * shouldRenderJsonWhen. Without that, Laravel answers these requests with
 * HTML or a 302 and none of the branches below can fire.
 *
 * @returns {Promise<{ok: boolean, status: number, data: object}>}
 */
export async function postJson(url, body = {}, options = {}) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let response;
    try {
        response = await fetch(url, {
            method: options.method ?? 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });
    } catch (error) {
        console.error('Network error:', error);
        showToast('Gagal terhubung ke server. Coba lagi.', 'error');

        return { ok: false, status: 0, data: {} };
    }

    // A body that isn't JSON must not mask the status code.
    let data = {};
    try {
        data = await response.json();
    } catch {
        data = {};
    }

    if (response.ok) {
        if (data.message && options.silentSuccess !== true) {
            showToast(data.message, 'success');
        }

        return { ok: true, status: response.status, data };
    }

    handleErrorResponse(response.status, data);

    return { ok: false, status: response.status, data };
}

function firstValidationError(data) {
    if (data.message) {
        return data.message;
    }

    const errors = data.errors ?? {};
    for (const messages of Object.values(errors)) {
        if (Array.isArray(messages) && messages.length > 0) {
            return messages[0];
        }
    }

    return null;
}

function handleErrorResponse(status, data) {
    switch (status) {
        case 422:
            showToast(firstValidationError(data) ?? 'Ada yang tidak beres dengan pesanan kamu.', 'warning');
            break;

        case 403:
            // EnsureTableSession rejects a session that hasn't opened this
            // table's page. That IS recoverable by reloading, but the
            // customer has no way to know it — so offer the fix directly.
            showToast(data.message ?? 'Kamu belum membuka halaman meja ini.', 'error', {
                actionLabel: 'Muat Ulang',
                onAction: () => window.location.reload(),
                autoDismiss: false,
            });
            break;

        case 419:
            // The CSRF token expired. Reloading refreshes the meta tag, and
            // there is nothing else the customer can usefully do.
            showToast('Sesi kamu kedaluwarsa. Halaman akan dimuat ulang.', 'warning');
            setTimeout(() => window.location.reload(), 2000);
            break;

        case 429:
            // Hardcoded in Indonesian on purpose: Laravel's built-in throttle
            // message is English ("Too Many Attempts.") and would otherwise
            // reach an Indonesian customer untranslated.
            showToast('Terlalu banyak permintaan. Tunggu sebentar ya.', 'warning');
            break;

        default:
            showToast(
                status >= 500
                    ? 'Terjadi kesalahan di server. Coba lagi sebentar lagi.'
                    : (data.message ?? 'Permintaan gagal diproses.'),
                'error',
            );
    }
}

// Confirmation prompts for destructive forms.
//
// These used to live in inline onsubmit="return confirm('... {{ $name }} ...')"
// attributes, which broke the moment a menu item was named something like
// Martabak 'Spesial': Blade escapes the apostrophe to &#039;, the browser
// decodes it back to ' while parsing the attribute, and the resulting
// JavaScript no longer parses — so the handler silently never ran and the
// form submitted with no confirmation at all. Reading the message from a
// data attribute instead keeps it as data and never as code.
document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');
    if (form && ! window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});

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

    initToasts();

    // --- Add-to-order ----------------------------------------------------
    // Menu page "+" buttons POST and get JSON back, so the sticky summary
    // bar (components/order-summary.blade.php) updates in place without a
    // reload. Phase 7 repoints these at the cart endpoint; the button-state
    // and summary-updating mechanics stay the same.
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

            // postJson surfaces the server's message itself — success and
            // every failure mode — so there is nothing to report here beyond
            // returning the button to a sane state.
            const { ok, data } = await postJson(url, {
                menu_item_id: button.dataset.menuItemId,
                quantity: 1,
            });

            if (ok && data.cart) {
                if (summaryCount) summaryCount.textContent = data.cart.item_count;
                if (summaryTotal) summaryTotal.textContent = data.cart.total_formatted;
                summaryBar?.classList.remove('translate-y-full');
                summaryBar?.classList.add('translate-y-0');

                showState('success');
                setTimeout(() => showState('idle'), 700);
            } else {
                showState('idle');
            }

            button.disabled = false;
        });
    });

    // --- Admin: payment method / cash-received / kembalian preview -------
    // Guarded on data-payment-method-group so this is a no-op on every
    // page except order-detail.blade.php while an order is still ongoing.
    // Pure UX: the real cash >= total check happens server-side in
    // ClearOrderRequest, this only saves the cashier a rejected submit.
    const paymentGroup = document.querySelector('[data-payment-method-group]');
    if (paymentGroup) {
        const cashFields = document.querySelector('[data-cash-fields]');
        const cashInput = document.querySelector('[data-cash-received-input]');
        const changePreview = document.querySelector('[data-change-preview]');
        const changeAmount = document.querySelector('[data-change-preview-amount]');
        const orderTotal = Number(cashInput?.dataset.orderTotal ?? 0);

        const formatRupiah = (value) => (value < 0 ? '-Rp ' : 'Rp ') + Math.abs(value).toLocaleString('id-ID');

        const updateCashVisibility = () => {
            const requiresCash = paymentGroup.querySelector('input:checked')?.dataset.requiresCash === '1';
            cashFields?.classList.toggle('hidden', !requiresCash);
            if (!requiresCash) {
                changePreview?.classList.add('hidden');
            }
        };

        const updateChangePreview = () => {
            if (!cashInput?.value) {
                changePreview?.classList.add('hidden');

                return;
            }

            const change = Number(cashInput.value) - orderTotal;
            changePreview?.classList.remove('hidden');

            if (changeAmount) {
                changeAmount.textContent = formatRupiah(change);
                changeAmount.classList.toggle('text-red-600', change < 0);
                changeAmount.classList.toggle('dark:text-red-400', change < 0);
            }
        };

        paymentGroup.addEventListener('change', () => {
            updateCashVisibility();
            updateChangePreview();
        });
        cashInput?.addEventListener('input', updateChangePreview);

        updateCashVisibility();
        updateChangePreview();
    }

    // --- Cart quantity steppers ------------------------------------------
    // Reaching 0 removes the line, so the minus button needs no separate
    // delete affordance. The page reloads on any change that alters the set
    // of lines, because re-deriving the whole cart in JS would duplicate the
    // Blade rendering — the exact duplication the server-side cart exists to
    // avoid.
    document.querySelectorAll('[data-cart-step]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (button.disabled) {
                return;
            }

            const quantity = Number(button.dataset.quantity);
            button.disabled = true;

            const { ok } = await postJson(
                button.dataset.url,
                {
                    menu_item_id: button.dataset.menuItemId,
                    quantity,
                },
                { method: 'PATCH', silentSuccess: true },
            );

            if (ok) {
                window.location.reload();

                return;
            }

            button.disabled = false;
        });
    });
});
