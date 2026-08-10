<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Table;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * The full menu, grouped by category, scoped to a table so every
     * "add" action on the page knows which table's tab to add to (wired
     * up in Phase 2 — for now the buttons are inert).
     */
    public function index(Table $table): View
    {
        $categories = Category::query()
            ->orderedBySort()
            ->with(['menuItems' => fn ($query) => $query->available()->orderedBySort()])
            ->get();

        return view('table.menu', [
            'table' => $table,
            'categories' => $categories,
            // Powers the sticky order-summary bar: if Agus is still
            // browsing after his 12pm matcha, it shows "1 item" right
            // away instead of looking like an empty tab.
            'order' => $table->activeOrder(),
        ]);
    }
}
