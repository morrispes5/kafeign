<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents one of the cafe's 36 physical dining tables.
 *
 * Named `Table` for the Eloquent model (matching the `tables` DB table) —
 * not to be confused with "database table" in code comments/docs.
 */
class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'number',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
        ];
    }

    /**
     * Route-model binding resolves {table} from the URL's table `number`
     * segment (e.g. /table/7) instead of the internal `id` primary key.
     */
    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'dining_table_id');
    }

    /**
     * The table's currently open tab, if any — null means the table is
     * free and entering it will start a brand-new order.
     */
    public function activeOrder(): ?Order
    {
        return $this->orders()
            ->where('status', OrderStatus::Ongoing)
            ->first();
    }
}
