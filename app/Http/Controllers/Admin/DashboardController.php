<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Every table currently mid-visit — an ongoing, unpaid tab — with its
     * item count and running total, newest-opened first. This is the
     * cashier's main screen: whatever shows up here is what's still
     * owed, and clearing a table (Admin\OrderController::clear) is what
     * makes it disappear from this list.
     */
    public function index(): View
    {
        $activeOrders = Order::query()
            ->ongoing()
            ->with(['table', 'orderItems'])
            ->latest('opened_at')
            ->get();

        return view('admin.dashboard', [
            'activeOrders' => $activeOrders,
            'totalTables' => Table::count(),
        ]);
    }
}
