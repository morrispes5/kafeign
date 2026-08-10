<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\AddOrderItemRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * The customer's running tab for this table — everything ordered
     * since it was opened, and the live total. Stays "ongoing" until the
     * cashier clears it from the admin dashboard (Phase 3).
     */
    public function show(Table $table): View
    {
        $order = $table->activeOrder()?->load('orderItems');

        return view('table.order', [
            'table' => $table,
            'order' => $order,
        ]);
    }

    /**
     * AJAX endpoint the menu page's "+" buttons call. Opens the table's
     * tab on the very first item (Order::findOrCreateOngoingForTable) and
     * returns just enough JSON for the sticky summary bar to update in
     * place — no full page reload.
     */
    public function store(AddOrderItemRequest $request, Table $table): JsonResponse
    {
        $menuItem = MenuItem::available()->findOrFail($request->integer('menu_item_id'));
        $quantity = $request->integer('quantity', 1);

        $order = Order::findOrCreateOngoingForTable($table);

        // The request-level `max:20` only caps a single tap. Without a
        // ceiling on the running line total, repeated calls could stack a
        // tab into the millions — nonsense for a cafe, and a cheap way to
        // grief a table. A real order never gets close to this.
        $existing = $order->orderItems()
            ->where('menu_item_id', $menuItem->id)
            ->sum('quantity');

        if ($existing + $quantity > Order::MAX_QUANTITY_PER_ITEM) {
            return response()->json([
                'message' => 'Jumlah pesanan untuk item ini sudah mencapai batas. Silakan hubungi staf kalau butuh lebih banyak.',
            ], 422);
        }

        $order->addItem($menuItem, $quantity);

        return response()->json([
            'message' => "{$menuItem->name} ditambahkan ke pesanan.",
            'order' => [
                'item_count' => $order->orderItems->sum('quantity'),
                'total' => $order->total,
                'total_formatted' => 'Rp '.number_format($order->total, 0, ',', '.'),
            ],
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
