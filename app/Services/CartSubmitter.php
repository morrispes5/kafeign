<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns a customer's private session cart into real order lines on the
 * table's shared tab — the moment the kitchen and the cashier first learn
 * the order exists.
 */
class CartSubmitter
{
    /**
     * All-or-nothing: if any line fails, nothing is written at all.
     *
     * Partial fulfilment was rejected deliberately. Silently sending 1 of
     * the 3 croissants somebody asked for means they find out at the
     * counter or when the food arrives — the most expensive possible place
     * to discover it. Rejecting costs the customer five seconds of editing
     * while they are sitting there holding the phone.
     *
     * @throws ValidationException when any line cannot be fulfilled
     */
    public function submit(Table $table, Cart $cart): Order
    {
        if ($cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang kamu masih kosong.',
            ]);
        }

        $lines = $cart->lines();

        // Resolve everything and collect every problem BEFORE opening the
        // transaction, so the customer gets the full list of what to fix in
        // one go rather than one error per attempt.
        $errors = [];
        $ongoing = $table->activeOrder();

        foreach ($lines as $line) {
            /** @var MenuItem $item */
            $item = $line['item'];

            if (! $item->is_available) {
                $errors["cart.{$item->id}"] = "{$item->name} sedang tidak tersedia. Hapus dari keranjang untuk melanjutkan.";

                continue;
            }

            $allowance = $cart->remainingAllowanceFor($item, $ongoing) + $line['quantity'];
            if ($line['quantity'] > $allowance) {
                $errors["cart.{$item->id}"] = "Jumlah {$item->name} melebihi batas pemesanan. Kurangi jumlahnya.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'cart' => 'Pesanan belum dikirim. Ada item yang perlu diperbaiki dulu.',
                ...$errors,
            ]);
        }

        $order = DB::transaction(function () use ($table, $lines) {
            $order = Order::findOrCreateOngoingForTable($table);

            foreach ($lines as $line) {
                // --- Phase 8 hook -------------------------------------
                // Stock is checked and decremented here, as one atomic
                // conditional UPDATE per line. It belongs INSIDE this
                // transaction so a shortfall on the last line rolls back
                // the lines already written above it.
                // ------------------------------------------------------

                // Order::addItem opens its own transaction; nested, that
                // becomes a savepoint. That is correct — do not "simplify"
                // it away.
                $order->addItem($line['item'], $line['quantity']);
            }

            // Tells the cashier's dashboard that something arrived since
            // they last looked (Phase 10). Set explicitly rather than via a
            // model hook: a hook touching its parent from inside the nested
            // addItem transaction is easy to trip over later.
            $order->forceFill(['last_item_added_at' => now()])->save();

            return $order;
        });

        $cart->clear();

        return $order->refresh();
    }
}
