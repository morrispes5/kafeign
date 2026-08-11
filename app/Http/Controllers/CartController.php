<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\UpdateCartRequest;
use App\Models\MenuItem;
use App\Models\Table;
use App\Services\CartSubmitter;
use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The customer's private basket, before anything reaches the cashier.
 *
 * Nothing here writes to `orders` — that only happens in submit(), which
 * is the single moment an order becomes visible to staff.
 */
class CartController extends Controller
{
    public function show(Table $table): View
    {
        $cart = Cart::forTable($table);

        return view('table.cart', [
            'table' => $table,
            'lines' => $cart->lines(),
            'total' => $cart->total(),
            'itemCount' => $cart->count(),
        ]);
    }

    /**
     * AJAX target for the menu page's "+" buttons.
     */
    public function store(UpdateCartRequest $request, Table $table): JsonResponse
    {
        $cart = Cart::forTable($table);
        $menuItem = $this->orderableItem($request->integer('menu_item_id'));
        $quantity = max(1, $request->integer('quantity', 1));

        if (! $cart->hasRoomForAnotherItem($menuItem)) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'Keranjang sudah penuh. Kirim dulu pesanan yang ada.',
            ]);
        }

        $allowance = $cart->remainingAllowanceFor($menuItem, $table->activeOrder());

        if ($allowance <= 0) {
            throw ValidationException::withMessages([
                'menu_item_id' => "Jumlah {$menuItem->name} sudah mencapai batas pemesanan.",
            ]);
        }

        $cart->add($menuItem, min($quantity, $allowance));

        return response()->json([
            'message' => "{$menuItem->name} ditambahkan ke keranjang.",
            'cart' => $this->summary($cart),
        ]);
    }

    /**
     * Quantity stepper on the cart page. Quantity 0 removes the line, so
     * the stepper's "minus" needs no special case.
     */
    public function update(UpdateCartRequest $request, Table $table): JsonResponse
    {
        $cart = Cart::forTable($table);
        $menuItem = $this->orderableItem($request->integer('menu_item_id'));
        $requested = $request->integer('quantity');

        $allowance = $cart->remainingAllowanceFor($menuItem, $table->activeOrder())
            + $cart->quantityOf($menuItem);

        if ($requested > $allowance) {
            throw ValidationException::withMessages([
                'quantity' => "Jumlah {$menuItem->name} melebihi batas pemesanan.",
            ]);
        }

        $cart->setQuantity($menuItem, $requested);

        return response()->json([
            'message' => $requested > 0
                ? "Jumlah {$menuItem->name} diperbarui."
                : "{$menuItem->name} dihapus dari keranjang.",
            'cart' => $this->summary($cart),
        ]);
    }

    public function destroy(Table $table, MenuItem $menuItem): RedirectResponse
    {
        Cart::forTable($table)->remove($menuItem);

        return redirect()
            ->route('table.cart', $table)
            ->with('info', "{$menuItem->name} dihapus dari keranjang.");
    }

    /**
     * The one action that makes the order real. Everything up to here was
     * private to this browser.
     */
    public function submit(Table $table, CartSubmitter $submitter): RedirectResponse
    {
        $cart = Cart::forTable($table);
        $count = $cart->count();

        try {
            $submitter->submit($table, $cart);
        } catch (InsufficientStockException $e) {
            // Someone took the last one between the customer opening the
            // cart and pressing Kirim. Nothing was written — send them
            // back with the specific line called out.
            throw ValidationException::withMessages([
                'cart' => 'Pesanan belum dikirim. Stok berubah saat kamu memesan.',
                ...$e->itemErrors,
            ]);
        }

        return redirect()
            ->route('table.order', $table)
            ->with('success', "{$count} item dikirim ke kasir.");
    }

    /**
     * Availability is gated here rather than in the FormRequest so the
     * message can name the item. MenuItem::available() is the single
     * source of truth for "may this be ordered right now".
     */
    private function orderableItem(int $menuItemId): MenuItem
    {
        $menuItem = MenuItem::find($menuItemId);

        if (! $menuItem || ! MenuItem::available()->whereKey($menuItemId)->exists()) {
            throw ValidationException::withMessages([
                'menu_item_id' => $menuItem
                    ? "{$menuItem->name} sedang tidak tersedia."
                    : 'Item menu tidak ditemukan.',
            ]);
        }

        return $menuItem;
    }

    /** @return array{item_count: int, total: int, total_formatted: string} */
    private function summary(Cart $cart): array
    {
        $total = $cart->total();

        return [
            'item_count' => $cart->count(),
            'total' => $total,
            'total_formatted' => 'Rp '.number_format($total, 0, ',', '.'),
        ];
    }
}
