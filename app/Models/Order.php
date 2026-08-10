<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * One row = one table's running tab/session, from the moment the first
 * item is added until the cashier clears it (status -> paid).
 */
class Order extends Model
{
    use HasFactory;

    /**
     * Ceiling on how many units of one menu item a single tab may hold.
     * Generous for a real table (even a large group), low enough that
     * abuse of the anonymous ordering endpoint stays a nuisance rather
     * than a several-million-rupiah bill.
     */
    public const MAX_QUANTITY_PER_ITEM = 50;

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
     * Adds a menu item to this tab. Re-ordering the same item at the same
     * price tops up the existing line's quantity, so the tab reads
     * "Kopi Susu Dekap x3" rather than three separate rows.
     *
     * The price is deliberately part of the match. If the admin changed
     * the price since the earlier units were ordered, this opens a new
     * line at the new price instead of repricing what the customer
     * already committed to — a tab must never retroactively charge more
     * (or less) for units that were ordered before the change.
     *
     * The (order_id, menu_item_id, item_price) unique index is what makes
     * the "top up rather than duplicate" behaviour safe under concurrent
     * taps; on the losing race we re-read and top up instead of failing.
     */
    public function addItem(MenuItem $menuItem, int $quantity = 1): OrderItem
    {
        $attributes = [
            'menu_item_id' => $menuItem->id,
            'item_price' => $menuItem->price,
        ];

        try {
            return DB::transaction(function () use ($attributes, $menuItem, $quantity) {
                $orderItem = $this->orderItems()
                    ->where($attributes)
                    ->lockForUpdate()
                    ->first();

                if ($orderItem) {
                    // Set-then-save rather than increment(): increment()
                    // skips the `saving` hook that recalculates subtotal,
                    // which would leave the line's total stale.
                    $orderItem->quantity += $quantity;
                    $orderItem->save();

                    return $orderItem;
                }

                return $this->orderItems()->create([
                    ...$attributes,
                    'item_name' => $menuItem->name,
                    'quantity' => $quantity,
                ]);
            });
        } catch (QueryException) {
            // Lost the race to an identical concurrent add — the other
            // request created the line, so top that one up instead.
            $orderItem = $this->orderItems()->where($attributes)->firstOrFail();
            $orderItem->quantity += $quantity;
            $orderItem->save();

            return $orderItem;
        }
    }
}
