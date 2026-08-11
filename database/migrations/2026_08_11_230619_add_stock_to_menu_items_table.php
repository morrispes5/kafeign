<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-item stock count.
     *
     * NULLABLE on purpose, and NULL means "tidak dilacak" rather than
     * zero. Two reasons, both of which would otherwise cause real damage:
     *
     * 1. A non-null default of 0 would mark all 69 seeded items sold out
     *    the instant this migration runs. Nullable makes the migration a
     *    behavioural no-op — nothing changes until the owner opts an item
     *    in by typing a number.
     * 2. A latte assembled from bulk beans and milk cannot meaningfully be
     *    counted in portions. Forcing a count on it guarantees a false
     *    "Habis" the first week nobody remembers to top it up. Countable
     *    things (pastry, cake, bottled drinks, snacks) opt in; everything
     *    else stays NULL forever.
     *
     * Note `unsignedInteger` buys nothing on SQLite — it has no unsigned
     * types and will happily store -3. The real guard against going
     * negative is the conditional WHERE in App\Services\StockLedger.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->unsignedInteger('stock')->nullable()->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
