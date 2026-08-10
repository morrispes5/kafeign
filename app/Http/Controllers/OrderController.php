<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddOrderItemRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Http\JsonResponse;
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

        $order = Order::findOrCreateOngoingForTable($table);
        $order->addItem($menuItem, $request->integer('quantity', 1));

        return response()->json([
            'message' => "{$menuItem->name} ditambahkan ke pesanan.",
            'order' => [
                'item_count' => $order->orderItems->sum('quantity'),
                'total' => $order->total,
                'total_formatted' => 'Rp '.number_format($order->total, 0, ',', '.'),
            ],
        ]);
    }
}
