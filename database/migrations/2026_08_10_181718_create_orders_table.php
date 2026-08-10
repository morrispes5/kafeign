<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row = one table's running tab/session. `status` is stored as a
     * plain string here and cast to the OrderStatus enum on the Eloquent
     * model (app/Enums/OrderStatus.php) — ongoing | paid | cancelled.
     *
     * The total is intentionally NOT stored here: it's computed on the fly
     * from order_items via an accessor on the Order model, so it can never
     * drift from the actual line items.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_table_id')->constrained('tables')->restrictOnDelete();
            $table->string('status')->default('ongoing');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['dining_table_id', 'status']);
        });

        // Core business invariant: a table can only have ONE ongoing order
        // at a time. This partial unique index enforces it at the database
        // level (SQLite supports partial indexes), so even a race condition
        // (e.g. a customer double-tapping the QR link) can never create two
        // ongoing tabs for the same table. Laravel's schema builder has no
        // first-class helper for partial unique indexes, so it's added via
        // a raw statement here.
        DB::statement(
            'create unique index unique_ongoing_order_per_table on orders (dining_table_id) where status = \'ongoing\''
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
