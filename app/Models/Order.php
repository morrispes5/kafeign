<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
