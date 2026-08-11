<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Table;
use App\Support\Cart;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * The full menu, grouped by category, scoped to a table so the "+"
     * buttons know which table's cart they are filling.
     */
    public function index(Table $table): View
    {
        $categories = Category::query()
            ->orderedBySort()
            // listedOnMenu(), NOT available(): a sold-out item must still
            // appear here, greyed out and badged "Habis". available() is
            // the write gate and would silently hide it instead.
            ->with(['menuItems' => fn ($query) => $query->listedOnMenu()->orderedBySort()])
            ->get();

        $cart = Cart::forTable($table);

        return view('table.menu', [
            'table' => $table,
            'categories' => $categories,
            // Both feed the sticky bottom bar, which shows the cart when
            // there is one and otherwise falls back to the table's tab.
            'cartCount' => $cart->count(),
            'cartTotal' => $cart->total(),
            'order' => $table->activeOrder(),
        ]);
    }
}
