@props(['selected' => null])

{{--
    Radio-button grid styled as clickable cards — no JS needed, the
    highlight on the chosen card comes from Tailwind's `has-[:checked]`
    variant reacting to the hidden radio input inside each label.
--}}
<div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
    @foreach (\App\Models\Category::ICONS as $key => $label)
        <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border border-kafeign-wood-soft px-2 py-3 text-center transition has-[:checked]:border-kafeign-maroon has-[:checked]:bg-kafeign-amber-soft dark:border-kafeign-ink-border dark:has-[:checked]:border-kafeign-amber dark:has-[:checked]:bg-kafeign-amber/10">
            <input type="radio" name="icon" value="{{ $key }}" class="sr-only" @checked($selected === $key) required>
            <x-icon :name="$key" class="h-5 w-5 text-kafeign-maroon-dark dark:text-kafeign-amber" />
            <span class="text-[11px] leading-tight text-kafeign-brown dark:text-kafeign-cream-soft">{{ $label }}</span>
        </label>
    @endforeach
</div>
