<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\OrderAlreadySettledException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClearOrderRequest;
use App\Models\Order;
use App\Services\ReceiptSequencer;
use App\Services\StockLedger;
use App\Support\BusinessDate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Every order that's left the active dashboard — paid or cancelled,
     * newest-closed first. The only route back to an old order once it's
     * off the dashboard; see Order::scopeClosed().
     */
    public function history(): View
    {
        $orders = Order::query()
            ->closed()
            ->with(['table', 'orderItems'])
            ->latest('closed_at')
            ->paginate(30);

        return view('admin.order-history', [
            'orders' => $orders,
        ]);
    }

    /**
     * Itemized view of one table's tab — same shape as the customer-
     * facing table/order.blade.php, plus the Clear Table / Cancel actions
     * only staff should be able to trigger. Also reachable for
     * already-paid/cancelled orders (read-only) via the history list, so
     * it doesn't assume the order is still ongoing.
     */
    public function show(Order $order): View
    {
        $order->load(['table', 'orderItems']);

        return view('admin.order-detail', [
            'order' => $order,
        ]);
    }

    /**
     * The printable struk. Paid orders only — an ongoing order has
     * nothing frozen yet to print, and a cancelled one was never paid.
     */
    public function receipt(Order $order): View|RedirectResponse
    {
        if ($order->status !== OrderStatus::Paid) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', 'Struk hanya tersedia untuk pesanan yang sudah lunas.');
        }

        $order->load(['table', 'orderItems']);

        return view('admin.receipt', [
            'order' => $order,
        ]);
    }

    /**
     * The cashier's "customer just paid at the register" button. Closes
     * the tab, freezes its total, mints a receipt number, and records how
     * it was paid — all in one atomic step so a double submit (double
     * click, two admin tabs on the same order) can never process the same
     * payment twice or burn two receipt numbers on it.
     */
    public function clear(ClearOrderRequest $request, Order $order, ReceiptSequencer $receipts): RedirectResponse
    {
        if ($order->status !== OrderStatus::Ongoing) {
            return back()->with('error', 'Pesanan ini sudah tidak aktif.');
        }

        $paymentMethod = PaymentMethod::from($request->validated('payment_method'));
        $cashReceived = $paymentMethod->requiresCashReceived()
            ? (int) $request->validated('cash_received')
            : null;

        try {
            DB::transaction(function () use ($order, $paymentMethod, $cashReceived, $receipts) {
                // Fresh read of the ACTUAL total right now, never the
                // copy the form was rendered with — ClearOrderRequest
                // already checked this once before the transaction
                // opened; this repeats it as the real guarantee, closing
                // the gap where a customer's cart submit lands between
                // those two checks.
                $liveTotal = (int) $order->orderItems()->sum('subtotal');

                if ($paymentMethod->requiresCashReceived() && $cashReceived < $liveTotal) {
                    throw ValidationException::withMessages([
                        'cash_received' => 'Total pesanan berubah, silakan cek ulang.',
                    ]);
                }

                $businessDate = BusinessDate::forMoment(now());
                $receiptNumber = $receipts->next($businessDate);

                // The atomic transition: only succeeds if the row is
                // STILL ongoing right now. A query-builder update(), not
                // $order->update() — deliberately bypasses Eloquent
                // mass-assignment (see Order::$fillable's comment) and
                // gives us the affected-row count a model save() doesn't.
                $affected = Order::query()
                    ->whereKey($order->id)
                    ->where('status', OrderStatus::Ongoing->value)
                    ->update([
                        'status' => OrderStatus::Paid->value,
                        'closed_at' => now(),
                        'payment_method' => $paymentMethod->value,
                        'cash_received' => $cashReceived,
                        'total_frozen' => $liveTotal,
                        'receipt_number' => $receiptNumber,
                        'business_date' => $businessDate->toDateString(),
                    ]);

                if ($affected === 0) {
                    // Lost the race — rolls back this whole transaction,
                    // including the receipt number just minted above, so
                    // no number is burned on a settlement that didn't
                    // actually happen.
                    throw new OrderAlreadySettledException();
                }
            });
        } catch (OrderAlreadySettledException $e) {
            return back()->with('error', $e->getMessage());
        }

        // The write above went through the query builder, not $order
        // itself, so this instance's attributes are still the pre-update
        // ones — must refresh before reading receipt_number/total_frozen.
        $order->refresh();

        return redirect()
            ->route('admin.orders.receipt', $order)
            ->with('success', "Meja {$order->table->number} sudah dibersihkan. Struk {$order->receipt_number} — total Rp"
                .number_format($order->total_frozen, 0, ',', '.').' diterima.');
    }

    /**
     * Edge case: a table opened a tab (e.g. tapped one item by mistake)
     * but the customer left without paying. Closes it without marking it
     * as paid revenue, while still keeping it in the DB as history. Never
     * touches any of the five payment columns — a cancelled order was
     * never paid, so it must never carry a receipt number.
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

        try {
            DB::transaction(function () use ($order, $restoreStock, $stock) {
                // Inside the same transaction as the atomic transition
                // below: if this request loses a double-submit race (the
                // conditional update finds the row no longer `ongoing`),
                // the restore rolls back too, instead of double-restoring
                // the same order's stock.
                if ($restoreStock) {
                    $stock->restoreOrder($order);
                }

                $affected = Order::query()
                    ->whereKey($order->id)
                    ->where('status', OrderStatus::Ongoing->value)
                    ->update([
                        'status' => OrderStatus::Cancelled->value,
                        'closed_at' => now(),
                    ]);

                if ($affected === 0) {
                    throw new OrderAlreadySettledException();
                }
            });
        } catch (OrderAlreadySettledException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Pesanan meja {$order->table->number} dibatalkan."
                .($restoreStock ? ' Stok dikembalikan ke menu.' : ' Stok tidak dikembalikan.'));
    }
}
