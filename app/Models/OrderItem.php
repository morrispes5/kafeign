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
        // whenever an order item is created, so callers never compute it
        // themselves and risk it drifting out of sync.
        static::creating(function (OrderItem $orderItem) {
            $orderItem->subtotal = $orderItem->item_price * $orderItem->quantity;
        });
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
