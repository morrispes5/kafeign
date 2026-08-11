<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Everything the cashier's payment step records. All five columns
     * are nullable and stay NULL forever for every order that is not
     * `paid` — an ongoing tab or a cancelled one never took a payment,
     * so it must never carry a receipt number or a payment method.
     *
     * payment_method   tunai/qris/kartu/transfer, cast to
     *                  App\Enums\PaymentMethod on the model.
     * cash_received    Only ever set alongside payment_method = cash.
     *                  "Kembalian" (change) is derived as
     *                  cash_received - total_frozen (Order::changeDue())
     *                  rather than stored as a third number that could
     *                  drift from the other two.
     * total_frozen     The order's total AT THE MOMENT of payment.
     *                  Order::total prefers this when it's set and
     *                  falls back to the live order_items sum otherwise
     *                  — the exact pre-Phase-9 behaviour — so every
     *                  order that predates this migration (this column
     *                  NULL) reads exactly as it did before. Named
     *                  total_frozen rather than total so it never sits
     *                  next to the `total` accessor under a confusing
     *                  shared name.
     * receipt_number   Human-facing printable number, format
     *                  YYYYMMDD-NNNN (e.g. 20260812-0001), minted once
     *                  by App\Services\ReceiptSequencer and frozen here
     *                  forever — never recomputed, so a future format
     *                  change can't retroactively alter an old receipt.
     * business_date    Which 04:00-cutoff business day this payment
     *                  belongs to (see App\Support\BusinessDate).
     *                  Computed once at payment time and stored, never
     *                  re-derived from closed_at later, so changing the
     *                  cutoff hour in config afterwards cannot
     *                  retroactively move an already-settled order into
     *                  a different day.
     *
     * receipt_number gets a PLAIN unique index, not a partial one like
     * unique_ongoing_order_per_table. That's correct here: a plain SQL
     * unique index already allows unlimited NULLs, so every
     * ongoing/cancelled row's NULL coexists freely and the index only
     * ever compares the always-non-null paid rows against each other.
     *
     * No backfill for orders paid before this migration — fabricating a
     * payment method or receipt number for a payment that predates this
     * feature would not be honest data. They simply keep all five
     * columns NULL, and the `total` fallback above (plus @if guards in
     * the history view) already handles them correctly.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('closed_at');
            $table->unsignedInteger('cash_received')->nullable()->after('payment_method');
            $table->unsignedInteger('total_frozen')->nullable()->after('cash_received');
            $table->string('receipt_number', 20)->nullable()->after('total_frozen');
            $table->date('business_date')->nullable()->after('receipt_number');

            $table->unique('receipt_number');
            // Nothing reads this yet (no sales-report page exists), but
            // a future report grouping/filtering by day is the obvious
            // next thing built on this column, and indexing now costs
            // nothing on a small table versus retrofitting it later.
            $table->index('business_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropIndex(['business_date']);
            $table->dropColumn(['payment_method', 'cash_received', 'total_frozen', 'receipt_number', 'business_date']);
        });
    }
};
