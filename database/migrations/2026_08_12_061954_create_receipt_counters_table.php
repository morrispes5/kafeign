<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per business day, holding the last sequential receipt
     * number issued that day. `business_date` is the cafe's 04:00-cutoff
     * day (see config('kafeign.business_day.start_hour') and
     * App\Support\BusinessDate), not the calendar date — a payment at
     * 00:10 belongs to the previous day's counter.
     *
     * The ONLY writer is App\Services\ReceiptSequencer, via a single
     * atomic "INSERT ... ON CONFLICT (business_date) DO UPDATE ...
     * RETURNING last_number" statement. That's deliberate, not
     * incidental: lockForUpdate() is a no-op on this app's SQLite
     * connection (see config/database.php), and a plain
     * increment()-then-separate-read would let two concurrent payments
     * each read back the OTHER's incremented value, handing out a
     * duplicate receipt number. One statement that both advances and
     * returns the count is the only safe shape here — same reasoning as
     * App\Services\StockLedger::take(), just an upsert instead of a
     * conditional decrement, because unlike a menu item, a business
     * day's counter row does not necessarily exist yet the first time
     * it's needed.
     */
    public function up(): void
    {
        Schema::create('receipt_counters', function (Blueprint $table) {
            $table->id();
            $table->date('business_date');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            // The actual ON CONFLICT target for the upsert above — this
            // index is not just documentation, the mint mechanism
            // requires it to exist.
            $table->unique('business_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_counters');
    }
};
