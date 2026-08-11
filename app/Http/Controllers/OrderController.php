<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use App\Models\Table;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The table's shared tab — items that have already been sent to the
 * cashier. Adding to it goes through CartController; this side only
 * displays it and handles a customer removing something.
 */
class OrderController extends Controller
{
    public function show(Table $table): View
    {
        $order = $table->activeOrder()?->load('orderItems');
        $cart = Cart::forTable($table);

        return view('table.order', [
            'table' => $table,
            'order' => $order,
            // Surfaced here so a customer who has items still sitting
            // unsent in their cart cannot mistake the tab for their whole
            // order — the cafe has no way to see an unsent cart.
            'cartCount' => $cart->count(),
            'cartTotal' => $cart->total(),
        ]);
    }

    /**
     * Removes a whole line item from the table's still-open tab (the
     * "changed my mind" button on table/order.blade.php). Only reachable
     * for the order item's own table and only while the order is still
     * ongoing — a closed tab is history and shouldn't be editable.
     *
     * If that was the last item on the tab, the now-empty order is
     * deleted outright rather than left sitting around as an empty
     * "ongoing" tab — otherwise the table would look occupied on the
     * admin dashboard for no reason, and the next visit here would land
     * on an empty order page instead of a fresh menu.
     */
    public function destroy(Table $table, OrderItem $orderItem): RedirectResponse
    {
        $order = $orderItem->order;

        if ($order->dining_table_id !== $table->id || $order->status !== OrderStatus::Ongoing) {
            abort(404);
        }

        $itemName = $orderItem->item_name;
        $orderItem->delete();

        if ($order->orderItems()->doesntExist()) {
            $order->delete();

            return redirect()
                ->route('table.menu', $table)
                ->with('info', "\"{$itemName}\" dihapus dari pesanan.");
        }

        return redirect()
            ->route('table.order', $table)
            ->with('info', "\"{$itemName}\" dihapus dari pesanan.");
    }
}
