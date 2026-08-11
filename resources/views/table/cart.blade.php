@extends('layouts.app')

@section('title', 'Keranjang — Meja ' . $table->number . ' — Kafeign')

@section('content')
    <div class="mb-6">
        <p class="text-xs uppercase tracking-wide text-kafeign-wood dark:text-kafeign-wood-soft">Meja {{ $table->number }}</p>
        <h1 class="font-display text-3xl font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">Keranjang</h1>
    </div>

    @if ($lines->isEmpty())
        <div class="rounded-xl border border-dashed border-kafeign-wood-soft p-8 text-center dark:border-kafeign-ink-border">
            <p class="text-sm text-kafeign-brown/70 dark:text-kafeign-cream-soft/70">
                Keranjang kamu masih kosong.
            </p>
            <a href="{{ route('table.menu', $table) }}"
                class="mt-4 inline-flex rounded-lg bg-kafeign-maroon px-5 py-2.5 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                Lihat Menu
            </a>
        </div>
    @else
        @error('cart')
            <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
                {{ $message }}
            </div>
        @enderror

        {{-- Stated plainly and up front: the cafe cannot see any of this yet. --}}
        <div class="mb-4 rounded-lg border-2 border-dashed border-kafeign-amber bg-kafeign-amber-soft px-4 py-3 text-sm text-kafeign-maroon-dark dark:border-kafeign-amber/60 dark:bg-kafeign-amber/10 dark:text-kafeign-amber">
            Item di bawah <strong>belum dikirim</strong> ke kasir. Tekan "Kirim Pesanan" supaya mulai dibuat.
        </div>

        <div data-cart-lines class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 px-4 dark:border-kafeign-ink-border/60 dark:bg-kafeign-ink-soft/30">
            @foreach ($lines as $line)
                @php $item = $line['item']; @endphp
                <div data-cart-line="{{ $item->id }}"
                    class="flex items-center justify-between gap-3 border-b border-kafeign-wood-soft/40 py-4 last:border-b-0 dark:border-kafeign-ink-border/60">
                    <div class="flex min-w-0 items-center gap-3">
                        @if ($item->image_url)
                            <img src="{{ $item->image_url }}" alt="" loading="lazy"
                                class="h-16 w-16 shrink-0 rounded-lg border border-kafeign-wood-soft/40 object-cover dark:border-kafeign-ink-border/60">
                        @endif
                        <div class="min-w-0">
                            <p class="font-medium text-kafeign-brown dark:text-kafeign-cream-soft">{{ $item->name }}</p>
                            <p class="text-xs text-kafeign-brown/60 dark:text-kafeign-cream-soft/60">
                                Rp {{ number_format($item->price, 0, ',', '.') }} / item
                            </p>
                            <p data-line-subtotal class="mt-0.5 font-display text-sm font-semibold text-kafeign-maroon-dark dark:text-kafeign-amber">
                                Rp {{ number_format($line['subtotal'], 0, ',', '.') }}
                            </p>

                            {{-- Points at the exact row that blocked the submit.
                                 A summary toast alone leaves the customer hunting
                                 for which item is the problem. --}}
                            @error("cart.{$item->id}")
                                <p class="mt-1 text-xs font-medium text-red-700 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Quantity stepper. Reaching 0 removes the line, so the
                         minus button needs no separate delete affordance. --}}
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" data-cart-step
                            data-url="{{ route('table.cart.items.update', $table) }}"
                            data-menu-item-id="{{ $item->id }}"
                            data-quantity="{{ $line['quantity'] - 1 }}"
                            aria-label="Kurangi {{ $item->name }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-kafeign-wood-soft text-kafeign-brown transition hover:bg-kafeign-cream-soft disabled:opacity-50 dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink">
                            <x-icon name="minus" class="h-4 w-4" />
                        </button>

                        <span data-line-quantity class="w-6 text-center font-display text-sm font-semibold text-kafeign-brown dark:text-kafeign-cream-soft">
                            {{ $line['quantity'] }}
                        </span>

                        <button type="button" data-cart-step
                            data-url="{{ route('table.cart.items.update', $table) }}"
                            data-menu-item-id="{{ $item->id }}"
                            data-quantity="{{ $line['quantity'] + 1 }}"
                            aria-label="Tambah {{ $item->name }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-kafeign-wood-soft text-kafeign-brown transition hover:bg-kafeign-cream-soft disabled:opacity-50 dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink">
                            <x-icon name="plus" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between rounded-xl bg-kafeign-maroon px-5 py-4 text-kafeign-cream dark:bg-kafeign-amber dark:text-kafeign-ink">
            <span class="text-sm font-medium uppercase tracking-wide">Total Keranjang</span>
            <span data-cart-total class="font-display text-xl font-semibold">
                Rp {{ number_format($total, 0, ',', '.') }}
            </span>
        </div>

        {{-- The confirmation spells out the consequence, because after this
             the customer can only remove items for a short window. --}}
        <form method="POST" action="{{ route('table.cart.submit', $table) }}" class="mt-6"
            data-confirm="Kirim {{ $itemCount }} item (Rp {{ number_format($total, 0, ',', '.') }}) ke kasir? Pesanan yang sudah dikirim hanya bisa dibatalkan beberapa menit setelahnya.">
            @csrf
            <button type="submit"
                class="w-full rounded-lg bg-kafeign-maroon py-3.5 text-sm font-semibold text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                Kirim Pesanan ke Kasir
            </button>
        </form>

        <a href="{{ route('table.menu', $table) }}"
            class="mt-3 flex items-center justify-center gap-1.5 rounded-lg border border-kafeign-wood-soft py-3 text-sm font-medium text-kafeign-brown transition hover:bg-kafeign-cream-soft dark:border-kafeign-ink-border dark:text-kafeign-cream-soft dark:hover:bg-kafeign-ink-soft">
            <x-icon name="plus" class="h-4 w-4" />
            Tambah Item Lain
        </a>
    @endif
@endsection
