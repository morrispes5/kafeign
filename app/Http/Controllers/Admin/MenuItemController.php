<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\UpdateMenuItemRequest;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->orderedBySort()
            ->with(['menuItems' => fn ($query) => $query->orderedBySort()])
            ->get();

        return view('admin.menu-items.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('admin.menu-items.create', [
            'categories' => Category::orderedBySort()->get(),
        ]);
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $data = $this->fromRequest($request);
        $data['sort_order'] ??= (int) MenuItem::where('category_id', $data['category_id'])->max('sort_order') + 1;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

        MenuItem::create($data);

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', "Item \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('admin.menu-items.edit', [
            'menuItem' => $menuItem,
            'categories' => Category::orderedBySort()->get(),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $this->fromRequest($request);

        if ($request->hasFile('image')) {
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        } elseif ($request->boolean('remove_image') && $menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
            $data['image_path'] = null;
        }

        $menuItem->update($data);

        return redirect()
            ->route('admin.menu-items.index')
            ->with('success', "Item \"{$menuItem->name}\" berhasil diperbarui.");
    }

    /**
     * order_items.menu_item_id is a restrict-on-delete foreign key on
     * purpose (see the order_items migration) — an item that's ever been
     * ordered can't be hard-deleted without corrupting past receipts.
     * "Nonaktifkan" (is_available = false) is the real way to retire an
     * item; this only allows true deletion for items nobody has ordered.
     */
    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->orderItems()->exists()) {
            return back()->with(
                'error',
                "Item \"{$menuItem->name}\" sudah pernah dipesan pelanggan, jadi tidak bisa dihapus permanen (supaya riwayat pesanan lama tidak rusak). Gunakan tombol Nonaktifkan sebagai gantinya."
            );
        }

        if ($menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return back()->with('success', "Item \"{$menuItem->name}\" dihapus.");
    }

    /**
     * One-click 86/un-86 from the index list, without opening the full
     * edit form.
     */
    public function toggleAvailability(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        $status = $menuItem->is_available ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Item \"{$menuItem->name}\" {$status}.");
    }

    /**
     * `sort_order` is deliberately left out of the array when the admin
     * submits it empty (the field is labelled optional). The column is
     * NOT NULL, so writing null would crash — each caller decides its own
     * fallback instead: a new item goes to the end of its category, an
     * edited item simply keeps the position it already had.
     */
    private function fromRequest(StoreMenuItemRequest|UpdateMenuItemRequest $request): array
    {
        return array_filter([
            'category_id' => $request->integer('category_id'),
            'name' => $request->string('name')->trim()->toString(),
            'price' => $request->integer('price'),
            'sort_order' => $request->filled('sort_order') ? $request->integer('sort_order') : null,
            'is_new' => $request->boolean('is_new'),
            'is_vdt' => $request->boolean('is_vdt'),
            'is_available' => $request->boolean('is_available'),
        ], fn ($value) => $value !== null);
    }
}
