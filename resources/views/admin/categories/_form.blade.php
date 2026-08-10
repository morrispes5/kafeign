{{-- Shared by create.blade.php and edit.blade.php. Expects an optional
     $category variable (null when creating). --}}
@php $category ??= null; @endphp

<div>
    <label for="name" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
        Nama Kategori
    </label>
    <input type="text" name="name" id="name" required value="{{ old('name', $category?->name) }}"
        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
    @error('name')
        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-4">
    <label for="sort_order" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
        Urutan Tampil <span class="normal-case text-kafeign-brown/50 dark:text-kafeign-cream-soft/50">(kosongkan untuk taruh di akhir)</span>
    </label>
    <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $category?->sort_order) }}"
        class="w-full rounded-lg border border-kafeign-wood-soft bg-kafeign-cream px-4 py-2.5 text-kafeign-brown focus:border-kafeign-maroon focus:outline-none dark:border-kafeign-ink-border dark:bg-kafeign-ink dark:text-kafeign-cream-soft">
    @error('sort_order')
        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mt-4">
    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">
        Ikon
    </label>
    <x-icon-picker :selected="old('icon', $category?->icon)" />
    @error('icon')
        <p class="mt-1.5 text-sm text-red-700 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
