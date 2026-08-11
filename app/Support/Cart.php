<?php

namespace App\Support;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Collection;

/**
 * A customer's not-yet-sent basket, held in the browser session.
 *
 * The cart deliberately stores NOTHING but `menu_item_id => quantity`.
 * Names and prices are looked up from `menu_items` fresh on every render
 * and again at submit, which kills two whole classes of problem at once:
 * a client can never dictate its own price, and the cart can never show a
 * price that has since changed.
 *
 * Why the session rather than the database: several people at one table
 * each order from their own phone, so each needs a private cart, but all
 * of them must land in the SAME ongoing order for that table. A session
 * is per-browser, so that falls out for free. A `draft` order status
 * would instead need a session-id column, garbage collection for
 * abandoned drafts, and would turn every future `Order::query()` into a
 * trap — one forgotten status filter and drafts leak into revenue.
 *
 * Keyed by table number because EnsureTableSession deliberately lets one
 * session hold several tables; without the key, a group that moves tables
 * would drag its cart along.
 *
 * The accepted trade-off: the cart dies with the session cookie, and
 * staff cannot see it. An unsent cart is invisible to the cafe — that is
 * the direct cost of the mis-tap protection this exists to provide, which
 * is why the UI shouts about unsent items in several places.
 */
class Cart
{
    public const SESSION_KEY = 'kafeign.cart';

    /** Guardrail against a session ballooning; a real table never gets close. */
    private const MAX_DISTINCT_ITEMS = 40;

    private function __construct(
        private readonly Table $table,
    ) {}

    public static function forTable(Table $table): self
    {
        return new self($table);
    }

    /**
     * Raw contents: [menu_item_id => quantity].
     *
     * @return array<int, int>
     */
    public function items(): array
    {
        return session(self::SESSION_KEY.'.'.$this->table->number, []);
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    /** Total number of units across all lines (not the number of lines). */
    public function count(): int
    {
        return array_sum($this->items());
    }

    public function quantityOf(MenuItem $menuItem): int
    {
        return $this->items()[$menuItem->id] ?? 0;
    }

    public function hasRoomForAnotherItem(MenuItem $menuItem): bool
    {
        return count($this->items()) < self::MAX_DISTINCT_ITEMS
            || array_key_exists($menuItem->id, $this->items());
    }

    /**
     * Cart contents resolved against the live menu, ready to render.
     *
     * Items that vanished or were retired while the customer browsed are
     * dropped from the session here rather than blowing up the page — the
     * customer simply sees them gone, and submit can never reference them.
     *
     * @return Collection<int, array{item: MenuItem, quantity: int, subtotal: int}>
     */
    public function lines(): Collection
    {
        $items = $this->items();

        if ($items === []) {
            return collect();
        }

        $menuItems = MenuItem::query()
            ->whereIn('id', array_keys($items))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->get();

        if ($menuItems->count() !== count($items)) {
            $this->write($menuItems->pluck('id')->flip()->map(fn ($_, $id) => $items[$id])->all());
        }

        return $menuItems->map(fn (MenuItem $item) => [
            'item' => $item,
            'quantity' => $items[$item->id],
            'subtotal' => $item->price * $items[$item->id],
        ])->values();
    }

    /** Rupiah total of everything currently in the cart. */
    public function total(): int
    {
        return (int) $this->lines()->sum('subtotal');
    }

    public function add(MenuItem $menuItem, int $quantity = 1): void
    {
        $this->setQuantity($menuItem, $this->quantityOf($menuItem) + $quantity);
    }

    public function setQuantity(MenuItem $menuItem, int $quantity): void
    {
        $items = $this->items();

        if ($quantity <= 0) {
            unset($items[$menuItem->id]);
        } else {
            $items[$menuItem->id] = $quantity;
        }

        $this->write($items);
    }

    public function remove(MenuItem $menuItem): void
    {
        $items = $this->items();
        unset($items[$menuItem->id]);

        $this->write($items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY.'.'.$this->table->number);
    }

    /**
     * How many more units of this item the customer may still add before
     * hitting the per-item ceiling — counting what is already on the
     * table's tab, since the cap applies to the finished order, not to the
     * cart in isolation.
     */
    public function remainingAllowanceFor(MenuItem $menuItem, ?Order $ongoingOrder): int
    {
        $alreadyOnTab = $ongoingOrder
            ? (int) $ongoingOrder->orderItems()->where('menu_item_id', $menuItem->id)->sum('quantity')
            : 0;

        return max(0, Order::MAX_QUANTITY_PER_ITEM - $alreadyOnTab - $this->quantityOf($menuItem));
    }

    /** @param array<int, int> $items */
    private function write(array $items): void
    {
        if ($items === []) {
            $this->clear();

            return;
        }

        session([self::SESSION_KEY.'.'.$this->table->number => $items]);
    }
}
