# Kafeign

Aplikasi pemesanan berbasis meja untuk kafe fisik "Kafeign" (Laravel 13 +
SQLite). Pelanggan pilih nomor meja (1–36) → pesan dari menu asli kafe →
pesanan jadi tab berjalan per meja sampai kasir membersihkannya lewat
dashboard admin.

**Baca [docs/README.md](docs/README.md) dulu sebelum mengerjakan apa
pun** — di situ ada konteks bisnis lengkap, tech stack, dan peta ke
dokumen lain ([FITUR.md](docs/FITUR.md) = fitur yang sudah jalan,
[ARSITEKTUR.md](docs/ARSITEKTUR.md) = skema DB/routes/model,
[CARA-MENJALANKAN.md](docs/CARA-MENJALANKAN.md) = cara run,
[ROADMAP.md](docs/ROADMAP.md) = apa yang sengaja belum dikerjakan).

## Fakta Cepat

- Semua UI Bahasa Indonesia, **tanpa emoji di mana pun** — fokus tipografi.
- Tanpa foto produk bawaan — cuma ikon SVG kategori kustom
  (`resources/views/components/icon.blade.php`). Admin bisa upload foto
  per item lewat dashboard, tidak ada default.
- Tema: coklat maroon/terracotta + krem + amber (dari foto kafe asli,
  BUKAN dari `legacy-static-site/` — itu arsip lama, tidak dipakai).
- Tidak ada JS framework — vanilla JS + `fetch()` saja
  (`resources/js/app.js`). Tailwind v4 pakai CSS-first config
  (`@theme` di `resources/css/app.css`), bukan `tailwind.config.js`.
- Invariant inti: **1 meja hanya boleh punya 1 order `ongoing`** —
  ditegakkan lewat partial unique index di migration `orders`, bukan
  cuma dicek di PHP. Jangan hapus/ubah index itu tanpa paham konsekuensinya.
- Invariant kedua: **`order_items` unik per (order, item, harga)**. Harga
  ikut jadi kunci supaya perubahan harga menu tidak pernah mengubah harga
  unit yang sudah terlanjur dipesan. Jangan "sederhanakan" jadi unik per
  (order, item) — itu mengembalikan bug penagihan yang sudah diperbaiki.
- Pemesanan pelanggan bersifat anonim, jadi endpoint order dilindungi
  berlapis (`EnsureTableSession` + throttle + batas jumlah). Batas
  perlindungannya dijelaskan jujur di `docs/ARSITEKTUR.md` — baca dulu
  sebelum mengubah apa pun di sekitar situ.
- **Alur pesan = keranjang dulu, baru dikirim** (Phase 7). Tap "+" hanya
  mengisi `App\Support\Cart` di session — kasir TIDAK melihat apa pun
  sampai `POST /table/{n}/cart/submit`. Keranjang menyimpan hanya
  `menu_item_id => qty`; nama & harga selalu dibaca ulang dari DB. Jangan
  pernah menaruh harga di session/klien.
- `bootstrap/app.php` — `shouldRenderJsonWhen` **harus** tetap meng-OR
  `expectsJson()`. Kalau dihapus, semua `fetch()` pelanggan diam-diam
  menerima HTML/302 lagi dan seluruh pesan error hilang tanpa jejak.
- Toast: JS **meng-clone `<template>`** dari `components/toast-host.blade.php`,
  tidak pernah merakit class di JS — Tailwind v4 akan memurge class yang
  tidak ada di file sumber, dan toast-nya tampil tanpa gaya di produksi.
- `lockForUpdate()` **tidak berfungsi di SQLite**. Pengaman balapan di
  project ini selalu unique index + catch-and-retry, bukan row lock.
- Kredensial admin ada di `.env` (`ADMIN_EMAIL`/`ADMIN_PASSWORD`) dan
  dibuat ulang oleh `AdminUserSeeder` tiap `migrate:fresh --seed`.
  **Akun yang dibuat di luar `.env` akan hilang saat DB di-reset** —
  ini pernah terjadi dan bikin user tidak bisa login. Kelola akun lewat
  `php artisan admin:list` / `admin:create` / `admin:unlock`, jangan
  lewat tinker. Jangan jalankan `migrate:fresh` tanpa memberi tahu user.

## Perintah yang Sering Dipakai

```bash
php artisan serve              # jalankan server dev
npm run dev                    # (opsional, terpisah) auto-rebuild CSS/JS
php artisan admin:list         # cek akun admin yang bisa login
php artisan admin:create       # buat akun admin / ganti password
php artisan admin:unlock --all # buka kunci login kena rate limit
php artisan migrate:fresh --seed  # HATI-HATI: hapus SEMUA data (pesanan, akun non-.env)
php artisan tinker             # inspeksi database langsung
```

Detail lengkap ada di [docs/CARA-MENJALANKAN.md](docs/CARA-MENJALANKAN.md).

## Cara Kerja dengan User Ini

- User memandu pengerjaan **bertahap per fase** (Phase 0–5, lihat
  [docs/FITUR.md](docs/FITUR.md)), diminta konfirmasi sebelum lanjut ke
  fase berikutnya — jangan loncat kerjakan fase yang belum diminta.
- User masih belajar Laravel/PHP — jelaskan keputusan teknis dengan
  bahasa yang jelas, jangan asumsikan familiar dengan istilah framework.
- Komunikasi dalam Bahasa Indonesia santai.
