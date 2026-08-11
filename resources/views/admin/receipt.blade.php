<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->receipt_number }} — Kafeign</title>

    {{-- Standalone on purpose: this page does NOT extend layouts.admin, so
         the admin nav/header can never end up on a printed receipt. Light
         mode only — a piece of paper has no dark mode — so no theme-toggle
         script either, unlike every other admin page. --}}
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-kafeign-cream text-kafeign-brown antialiased">

    <div class="mx-auto max-w-sm px-4 py-8">
        <div class="print:hidden mb-6 flex items-center justify-between">
            <a href="{{ route('admin.orders.show', $order) }}" class="text-sm text-kafeign-brown/70 transition hover:text-kafeign-maroon-dark">
                &larr; Kembali ke Detail
            </a>
            <button type="button" onclick="window.print()"
                class="rounded-lg bg-kafeign-maroon px-4 py-2 text-sm font-medium text-kafeign-cream transition hover:bg-kafeign-maroon-dark">
                Cetak Struk
            </button>
        </div>

        <div class="rounded-xl border border-kafeign-wood-soft/50 bg-kafeign-cream-soft/40 p-6 print:rounded-none print:border-0 print:bg-white print:p-0">
            <div class="text-center">
                <p class="font-display text-2xl font-semibold tracking-wide text-kafeign-maroon-dark">KAFEIGN</p>
                <p class="mt-1 text-xs text-kafeign-brown/70">Meja {{ $order->table->number }}</p>
            </div>

            <div class="my-4 border-t border-dashed border-kafeign-wood-soft"></div>

            <dl class="grid grid-cols-2 gap-y-1 text-xs text-kafeign-brown/80">
                <dt>No. Struk</dt>
                <dd class="text-right font-medium text-kafeign-brown">{{ $order->receipt_number }}</dd>
                <dt>Tanggal</dt>
                <dd class="text-right font-medium text-kafeign-brown">{{ $order->closed_at->format('d M Y, H:i') }}</dd>
            </dl>

            <div class="my-4 border-t border-dashed border-kafeign-wood-soft"></div>

            <div>
                @foreach ($order->orderItems as $item)
                    <div class="flex items-start justify-between gap-3 py-1 text-sm">
                        <div>
                            <p class="text-kafeign-brown">{{ $item->item_name }}</p>
                            <p class="text-xs text-kafeign-brown/60">{{ $item->quantity }} &times; Rp {{ number_format($item->item_price, 0, ',', '.') }}</p>
                        </div>
                        <span class="shrink-0 font-medium text-kafeign-brown">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="my-4 border-t border-dashed border-kafeign-wood-soft"></div>

            <div class="flex items-center justify-between text-base font-semibold text-kafeign-maroon-dark">
                <span>Total</span>
                <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>

            <dl class="mt-3 grid grid-cols-2 gap-y-1 text-xs text-kafeign-brown/80">
                <dt>Metode Bayar</dt>
                <dd class="text-right font-medium text-kafeign-brown">{{ $order->payment_method?->label() ?? '—' }}</dd>

                @if ($order->payment_method?->requiresCashReceived())
                    <dt>Uang Diterima</dt>
                    <dd class="text-right font-medium text-kafeign-brown">Rp {{ number_format($order->cash_received, 0, ',', '.') }}</dd>
                    <dt>Kembalian</dt>
                    <dd class="text-right font-medium text-kafeign-brown">Rp {{ number_format($order->change_due, 0, ',', '.') }}</dd>
                @endif
            </dl>

            <div class="my-4 border-t border-dashed border-kafeign-wood-soft"></div>

            <p class="text-center text-xs text-kafeign-brown/60">Terima kasih sudah mampir ke Kafeign.</p>
        </div>
    </div>

</body>
</html>
