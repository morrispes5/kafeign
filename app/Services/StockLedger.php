<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;

/**
 * The only place in the codebase that writes `menu_items.stock`.
 *
 * Keeping every movement behind one class is what makes the rule below
 * checkable by reading a single file:
 *
 *   Stock is decremented exactly once, at cart submit.
 *   It is incremented only when an order line is un-made.
 *   It is NEVER touched at payment — those goods were sold.
 *
 * Items with NULL stock are not tracked; every method here leaves them
 * alone without needing a special case (see the SQL notes below).
 */
class StockLedger
{
    /**
     * Atomically take $quantity off an item's stock.
     *
     * Check and write must be a SINGLE statement. lockForUpdate() is a
     * no-op on SQLite — its grammar compiles the lock clause to an empty
     * string — so a read-then-write would leave a window in which two
     * customers can both pass the check and drive stock negative.
     *
     * The conditional WHERE does the work instead: if the row no longer
     * satisfies it, zero rows are affected and we know we lost the race.
     * On a NULL-stock row the whereNull branch matches and SQLite
     * evaluates `NULL - n` as NULL, so untracked items stay untracked.
     *
     * @return bool false when there was not enough stock
     */
    public function take(MenuItem $menuItem, int $quantity): bool
    {
        $affected = MenuItem::query()
            ->whereKey($menuItem->id)
            ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>=', $quantity))
            ->decrement('stock', $quantity);

        return $affected > 0;
    }

    /**
     * Put $quantity back — used when a line is removed or an order is
     * cancelled, never when it is paid.
     *
     * The whereNotNull guard keeps an untracked item from being given a
     * number it never had: `increment` on NULL would otherwise leave it
     * NULL anyway in SQLite, but being explicit documents the intent.
     */
    public function restore(MenuItem $menuItem, int $quantity): void
    {
        MenuItem::query()
            ->whereKey($menuItem->id)
            ->whereNotNull('stock')
            ->increment('stock', $quantity);
    }

    /** Restores a single order line's quantity. */
    public function restoreLine(OrderItem $orderItem): void
    {
        if ($orderItem->menuItem) {
            $this->restore($orderItem->menuItem, $orderItem->quantity);
        }
    }

    /**
     * Restores every line on an order — the cashier-cancels case.
     *
     * Whether this runs at all is the cashier's call at cancel time: if
     * the barista already made the drinks, putting the count back would
     * make the cafe over-sell tomorrow.
     */
    public function restoreOrder(Order $order): void
    {
        foreach ($order->orderItems()->with('menuItem')->get() as $orderItem) {
            $this->restoreLine($orderItem);
        }
    }
}
