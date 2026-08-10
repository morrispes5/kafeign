<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One tab may only hold a single line per (menu item + price paid).
     *
     * Two things depend on this:
     *
     * 1. Order::addItem() merges a repeat order of the same item into the
     *    existing line's quantity. Without a constraint that was a
     *    read-then-write race: two near-simultaneous taps could both see
     *    "no existing line" and each insert one, silently breaking the
     *    "Matcha x2" guarantee into two separate Matcha rows.
     * 2. item_price is part of the key on purpose. If the admin changes a
     *    price mid-visit, the already-ordered units must keep the price
     *    they were sold at, so the newly ordered units land on their own
     *    line at the new price instead of repricing the old ones.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(['order_id', 'menu_item_id', 'item_price'], 'order_items_unique_line');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_unique_line');
        });
    }
};
