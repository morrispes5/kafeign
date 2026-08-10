<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;

/**
 * One row = one table's running tab/session, from the moment the first
 * item is added until the cashier clears it (status -> paid).
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'dining_table_id',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'dining_table_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The running total, computed from line items rather than stored, so
     * it can never drift from what was actually ordered.
     */
    protected function total(): Attribute
    {
        return Attribute::get(fn () => $this->orderItems->sum('subtotal'));
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', OrderStatus::Ongoing);
    }

    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::Paid);
    }

    /**
     * Finds the table's currently open tab, or opens a new one if this is
     * the first item ordered this visit. This is the heart of the "Agus"
     * scenario: whatever table number the customer enters, they always
     * land on the one ongoing order for that table, never a duplicate.
     *
     * The real safety net is the partial unique index in the orders
     * migration (only one status=ongoing row per table, enforced by
     * SQLite itself) — the try/catch here just means that if two requests
     * both race to open the first order for the same table, the loser
     * re-queries and gets the winner's row instead of a hard failure.
     */
    public static function findOrCreateOngoingForTable(Table $table): self
    {
        $existing = static::query()
            ->where('dining_table_id', $table->id)
            ->ongoing()
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            return static::create([
                'dining_table_id' => $table->id,
                'status' => OrderStatus::Ongoing,
                'opened_at' => now(),
            ]);
        } catch (QueryException) {
            return static::query()
                ->where('dining_table_id', $table->id)
                ->ongoing()
                ->firstOrFail();
        }
    }

    /**
     * Adds a menu item to this tab. Re-adding an item already on the tab
     * tops up its quantity (and refreshes the name/price snapshot to the
     * item's current values) instead of creating a duplicate line — the
     * tab reads as "Kopi Susu Dekap x3", not three separate rows.
     */
    public function addItem(MenuItem $menuItem, int $quantity = 1): OrderItem
    {
        $orderItem = $this->orderItems()->firstOrNew(['menu_item_id' => $menuItem->id]);

        $orderItem->item_name = $menuItem->name;
        $orderItem->item_price = $menuItem->price;
        $orderItem->quantity = ($orderItem->quantity ?? 0) + $quantity;
        $orderItem->save();

        return $orderItem;
    }
}
