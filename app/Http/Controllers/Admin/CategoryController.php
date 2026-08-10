<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->orderedBySort()
                ->withCount('menuItems')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['sort_order'] ??= (int) Category::max('sort_order') + 1;

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori \"{$data['name']}\" berhasil ditambahkan.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], ignoreId: $category->id);
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori \"{$category->name}\" berhasil diperbarui.");
    }

    /**
     * Categories cascade-delete their menu items at the database level
     * (see the menu_items migration), which is exactly the kind of
     * silent bulk data loss an admin form should never trigger by
     * accident — so deletion is only allowed once the category is
     * already empty.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->menuItems()->exists()) {
            return back()->with(
                'error',
                "Kategori \"{$category->name}\" masih punya item menu. Pindahkan atau hapus item-nya dulu sebelum menghapus kategori ini."
            );
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori \"{$category->name}\" dihapus.");
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
