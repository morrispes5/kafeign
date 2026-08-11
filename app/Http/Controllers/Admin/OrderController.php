<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Itemized view of one table's tab — same shape as the customer-
     * facing table/order.blade.php, plus the Clear Table / Cancel actions
     * only staff should be able to trigger. Also reachable for
     * already-paid/cancelled orders (read-only) via order history links,
     * so it doesn't assume the order is still ongoing.
     */
    public function show(Order $order): View
    {
        $order->load(['table', 'orderItems']);

        return view('admin.order-detail', [
            'order' => $order,
        ]);
    }

    /**
     * The cashier's "customer just paid at the register" button. Closes
     * the tab so the table shows as free again — the next customer who
     * enters this table number gets a brand-new order, never this one.
     */
    public function clear(Order $order): RedirectResponse
    {
        if ($order->status !== OrderStatus::Ongoing) {
            return back()->with('error', 'Pesanan ini sudah tidak aktif.');
        }

        $order->update([
            'status' => OrderStatus::Paid,
            'closed_at' => now(),
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Meja {$order->table->number} sudah dibersihkan. Total Rp ".number_format($order->total, 0, ',', '.').' diterima.');
    }

    /**
     * Edge case: a table opened a tab (e.g. tapped one item by mistake)
     * but the customer left without paying. Closes it without marking it
     * as paid revenue, while still keeping it in the DB as history.
     */
    public function cancel(Request $request, Order $order, StockLedger $stock): RedirectResponse
    {
        if ($order->status !== OrderStatus::Ongoing) {
            return back()->with('error', 'Pesanan ini sudah tidak aktif.');
        }

        // The cashier decides, because only they know whether the food was
        // already made. Default is to restore (the common case is a
        // customer who left before anything was prepared), but ticking it
        // off matters: handing back stock for drinks already poured makes
        // the cafe over-sell tomorrow.
        $restoreStock = $request->boolean('restore_stock');

        if ($restoreStock) {
            $stock->restoreOrder($order);
        }

        $order->update([
            'status' => OrderStatus::Cancelled,
            'closed_at' => now(),
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Pesanan meja {$order->table->number} dibatalkan."
                .($restoreStock ? ' Stok dikembalikan ke menu.' : ' Stok tidak dikembalikan.'));
    }
}
