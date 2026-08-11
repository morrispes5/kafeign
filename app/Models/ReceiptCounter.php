<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-side only, so `receipt_counters` is inspectable from
 * `php artisan tinker` like every other table in this app (see
 * docs/CARA-MENJALANKAN.md). App\Services\ReceiptSequencer is the only
 * real writer, and it writes via a raw atomic upsert — never through
 * this model's create()/update()/save(). $fillable is deliberately
 * empty: nothing should ever mass-assign a counter row through Eloquent.
 */
class ReceiptCounter extends Model
{
    protected $fillable = [];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'last_number' => 'integer',
        ];
    }
}
