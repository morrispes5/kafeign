<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A single line item added to an order's tab. `item_name` and
     * `item_price` are snapshots of the menu item at the moment it was
     * ordered — kept separate from the live `menu_items` row so that past
     * orders still reflect what the customer was actually charged even if
     * the menu price changes later. `subtotal` is stored (not computed)
     * because it's immutable once the row is created.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Kept for reporting/joins even though name/price are snapshotted.
            $table->foreignId('menu_item_id')->constrained()->restrictOnDelete();
            $table->string('item_name', 150);
            $table->unsignedInteger('item_price');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('subtotal');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
