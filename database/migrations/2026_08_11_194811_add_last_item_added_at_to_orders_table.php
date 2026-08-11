<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When the tab last received items from a customer's cart.
     *
     * Written by App\Services\CartSubmitter at submit time. `updated_at`
     * cannot stand in for this: it moves for unrelated reasons (an admin
     * cancelling, a settlement, any future column change), so it would
     * make the cashier's "new items" indicator fire on things that are not
     * new items.
     *
     * Consumed by the dashboard's "Baru" pill, which compares this against
     * `acknowledged_at` (added later, with the dashboard work).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('last_item_added_at')->nullable()->after('opened_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('last_item_added_at');
        });
    }
};
