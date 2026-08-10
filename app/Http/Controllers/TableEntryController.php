<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnterTableRequest;
use App\Models\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TableEntryController extends Controller
{
    /**
     * The very first screen a customer sees (either typed in manually or
     * opened via the table's QR code): "what table are you at?"
     */
    public function showEntryForm(): View
    {
        return view('table.entry');
    }

    /**
     * Handles the manual entry form submit and sends the customer to the
     * canonical per-table URL, which is what a QR code would point at
     * directly.
     */
    public function enter(EnterTableRequest $request): RedirectResponse
    {
        return redirect()->route('table.show', ['table' => $request->integer('number')]);
    }

    /**
     * QR deep-link landing spot for a specific table.
     *
     * If the table already has an ongoing tab (e.g. Agus came back an
     * hour later and re-scanned the same table's QR code), send them
     * straight to it instead of the menu — otherwise there's nothing
     * ordered yet, so send them to browse.
     */
    public function show(Table $table): RedirectResponse
    {
        if ($table->activeOrder()) {
            return redirect()->route('table.order', $table);
        }

        return redirect()->route('table.menu', $table);
    }
}
