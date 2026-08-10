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
     * Phase 1: just forwards straight to the menu — there's no ordering
     * concept yet. Phase 2 will make this check for an ongoing order first
     * and show the running tab instead when one already exists.
     */
    public function show(Table $table): RedirectResponse
    {
        return redirect()->route('table.menu', $table);
    }
}
