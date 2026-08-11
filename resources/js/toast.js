// Toast notifications.
//
// Markup comes from <template> blocks authored in Blade
// (resources/views/components/toast-host.blade.php) and cloned here.
// Building class strings in JS would break in production: Tailwind v4
// scans source files, so classes that only exist in a JS string get
// purged and the toast renders unstyled.
//
// Message text is always set via textContent, never innerHTML — menu item
// names legitimately contain apostrophes and this codebase has already been
// bitten once by treating item names as markup.

const AUTO_DISMISS_MS = {
    success: 4000,
    info: 4000,
    // Errors and warnings stay longer: they need reading, and the customer
    // may be mid-conversation with a friend when one appears.
    warning: 8000,
    error: 8000,
};

const MAX_VISIBLE = 3;

function host() {
    return document.querySelector('[data-toast-host]');
}

function dismiss(toast) {
    toast.remove();
}

/**
 * @param {string} message
 * @param {'success'|'error'|'warning'|'info'} type
 * @param {{actionLabel?: string, onAction?: () => void, autoDismiss?: boolean}} options
 */
export function showToast(message, type = 'info', options = {}) {
    const container = host();
    const template = document.querySelector(`[data-toast-template="${type}"]`);

    // No host on the page (or an unknown type) must never break the calling
    // flow — the action itself already succeeded or failed on the server.
    if (!container || !template) {
        return;
    }

    const toast = template.content.firstElementChild.cloneNode(true);
    toast.querySelector('[data-toast-message]').textContent = message;

    const actionButton = toast.querySelector('[data-toast-action]');
    if (options.actionLabel && options.onAction) {
        actionButton.textContent = options.actionLabel;
        actionButton.classList.remove('hidden');
        actionButton.addEventListener('click', options.onAction);
    }

    toast.querySelector('[data-toast-dismiss]').addEventListener('click', () => dismiss(toast));

    container.prepend(toast);

    // Keep the stack short so a burst of taps can't wall off the screen.
    while (container.children.length > MAX_VISIBLE) {
        container.lastElementChild.remove();
    }

    if (options.autoDismiss !== false) {
        setTimeout(() => dismiss(toast), AUTO_DISMISS_MS[type] ?? 4000);
    }

    return toast;
}

/**
 * Wires dismiss buttons on the server-rendered flash toasts, which exist in
 * the DOM before any JS runs.
 */
export function initToasts() {
    document.querySelectorAll('[data-toast-host] [data-toast-dismiss]').forEach((button) => {
        button.addEventListener('click', () => button.closest('div.pointer-events-auto')?.remove());
    });
}
