<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single line item on an order's tab. `item_name`/`item_price` are
 * snapshots taken at order time so historical tabs stay accurate even if
 * the live menu item's name or price changes later.
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'item_name',
        'item_price',
        'quantity',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'item_price' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // subtotal is always item_price * quantity — derived automatically
        // whenever an order item is created OR updated (e.g. Order::addItem()
        // topping up the quantity of an existing line), so callers never
        // compute it themselves and risk it drifting out of sync. `saving`
        // fires on both inserts and updates, unlike `creating`.
        static::saving(function (OrderItem $orderItem) {
            $orderItem->subtotal = $orderItem->item_price * $orderItem->quantity;
        });
    }

    /**
     * Whether the customer may still remove this line themselves.
     *
     * Measured from `updated_at`, which moves when the line is topped up —
     * so adding two more of something restarts its window, which is the
     * behaviour you want: the newest units are the ones not yet made.
     *
     * A window of 0 disables the limit entirely (the pre-Phase-8
     * behaviour: cancel any time, and accept the stock drift).
     */
    public function isSelfDeletable(): bool
    {
        $windowMinutes = (int) config('kafeign.order.self_delete_window_minutes');

        if ($windowMinutes <= 0) {
            return true;
        }

        return $this->updated_at?->gt(now()->subMinutes($windowMinutes)) ?? true;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
