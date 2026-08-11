{{-- Shared by create.blade.php and edit.blade.php. Expects $categories
     and an optional $menuItem variable (null when creating). The parent
     view is responsible for wrapping this in a <form enctype="multipart/form-data">. --}}
@php $menuItem ??= null; @endphp

<div>
    <label for="category_id" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
        Kategori
    </label>
    <select name="category_id" id="category_id" required
        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
        <option value="" disabled {{ old('category_id', $menuItem?->category_id) ? '' : 'selected' }}>Pilih kategori</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((int) old('category_id', $menuItem?->category_id) === $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-4">
    <label for="name" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
        Nama Item
    </label>
    <input type="text" name="name" id="name" required value="{{ old('name', $menuItem?->name) }}"
        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
    @error('name')
        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-4 grid grid-cols-2 gap-4">
    <div>
        <label for="price" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
            Harga (Rp)
        </label>
        <input type="number" name="price" id="price" required min="0" step="500" value="{{ old('price', $menuItem?->price) }}"
            class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
        @error('price')
            <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sort_order" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
            Urutan <span class="normal-case text-kafeign-brown/50 dark:text-kafeign-cream-soft/50">(opsional)</span>
        </label>
        <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $menuItem?->sort_order) }}"
            class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
    </div>
</div>

<div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
    <label class="flex items-center gap-2 text-sm text-kafeign-brown dark:text-kafeign-cream-soft">
        <input type="checkbox" name="is_new" value="1" @checked(old('is_new', $menuItem?->is_new))
            class="rounded border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
        Badge "Baru"
    </label>
    <label class="flex items-center gap-2 text-sm text-kafeign-brown dark:text-kafeign-cream-soft">
        <input type="checkbox" name="is_vdt" value="1" @checked(old('is_vdt', $menuItem?->is_vdt))
            class="rounded border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
        Badge "VDT"
    </label>
    <label class="flex items-center gap-2 text-sm text-kafeign-brown dark:text-kafeign-cream-soft">
        <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $menuItem?->is_available ?? true))
            class="rounded border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
        Tersedia (muncul di menu pelanggan)
    </label>
</div>

<div class="mt-5 border-t border-kafeign-wood-soft/40 pt-4 dark:border-kafeign-ink-border/60">
    <label for="image" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
        {{-- Limit read from config, never hardcoded: this label once said
             2MB while the server accepted 10MB, so uploads the admin was
             told to expect kept failing. --}}
        Foto Item <span class="normal-case text-kafeign-brown/50 dark:text-kafeign-cream-soft/50">(opsional, JPG/PNG/WebP maks {{ (int) (config('kafeign.menu_photo.max_kb') / 1024) }}MB — foto besar otomatis dikecilkan)</span>
    </label>

    @if ($menuItem?->image_url)
        <div class="mb-3 flex items-center gap-3">
            <img src="{{ $menuItem->image_url }}" alt="" class="h-16 w-16 rounded-lg object-cover">
            <label class="flex items-center gap-2 text-sm text-kafeign-brown dark:text-kafeign-cream-soft">
                <input type="checkbox" name="remove_image" value="1"
                    class="rounded border-kafeign-wood-soft text-kafeign-maroon focus:ring-kafeign-maroon">
                Hapus foto ini
            </label>
        </div>
    @endif

    <input type="file" name="image" id="image" accept="image/*"
        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-sm text-kafeign-brown file:mr-3 file:rounded-md file:border-0 file:bg-kafeign-maroon file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-kafeign-cream dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
    @error('image')
        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
