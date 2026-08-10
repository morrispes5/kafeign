# Fitur yang Sudah Jalan

Status per fase pengerjaan. Semua yang ditandai ✅ sudah diverifikasi
langsung (browser + database + kadang `tinker`/curl), bukan cuma ditulis
kodenya. Kalau menemukan sesuatu di sini yang ternyata sudah tidak akurat
(kode berubah tapi dokumen ini lupa di-update), percaya kodenya, lalu
tolong perbaiki file ini.

---

## Phase 0 — Database & Data Asli ✅

- SQLite di `database/database.sqlite`, 5 migration inti: `categories`,
  `menu_items`, `tables`, `orders`, `order_items`.
- **Invariant utama sistem**: satu meja hanya boleh punya **satu order
  berstatus `ongoing`** dalam satu waktu. Ditegakkan di level database
  lewat partial unique index di migration `orders`
  (`unique_ongoing_order_per_table`), bukan cuma dicek di kode PHP.
- Seed data **asli** dari foto papan menu kafe: 9 kategori, 69 item menu,
  36 meja (nomor 1–36), 1 akun admin (kredensial dari `.env`).
- Harga "Ice Yakult Espresso" sempat ambigu di foto sumber — di-seed
  25.000 sebagai estimasi, belum dikonfirmasi ke pemilik kafe.

## Phase 1 — Tampilan Depan Pelanggan ✅

- `GET /` — landing page welcome: hero + **satu dropdown** pilih meja
  1–36 (bukan grid tombol — sempat direvisi dari versi awal karena
  kepenuhan di 1 layar).
- `GET /table/{nomor}/menu` — menu dikelompokkan per kategori, dengan
  quick-nav chip yang bisa di-scroll horizontal, badge "Baru"/"VDT".
- Tema warna coklat maroon/krem/amber, font Fraunces + Instrument Sans,
  **tanpa emoji, tanpa foto produk** (cuma ikon SVG kategori).
- Dark mode toggle (localStorage) + sidebar navigasi off-canvas — di-port
  dari `legacy-static-site/js/main.js` tapi tanpa emoji.
- Sudah dites di viewport mobile (375px) — prioritas utama karena
  pelanggan akan scan QR pakai HP.

## Phase 2 — Logic Pemesanan ✅

- Tombol "+" di tiap item menu **aktif dan fungsional** — klik langsung
  `POST /table/{nomor}/order-items` lewat `fetch()` (AJAX, tanpa reload).
- **Sticky bar** di bawah layar halaman menu, nampilkan jumlah item +
  total, update live setelah nambah item.
- `GET /table/{nomor}/order` — halaman "Pesanan Kamu": rincian tab
  berjalan (nama, qty, subtotal per baris, total keseluruhan).
- **Perilaku "resume tab"**: masuk ke `/table/{nomor}` (link dari QR)
  otomatis cek — kalau ada order `ongoing` untuk meja itu, langsung
  diarahkan ke halaman pesanan (bukan menu kosong); kalau belum ada,
  diarahkan ke menu untuk mulai pesan.
- Nambah item yang **sama** dua kali **menggabung quantity** di baris
  yang sama (`Order::addItem()`), bukan bikin baris baru.
- Setiap meja **terisolasi total** — dites eksplisit, pesanan meja 2 dan
  meja 7 tidak pernah tercampur.
- Harga & nama di tiap baris pesanan adalah **snapshot** saat dipesan
  (kolom `item_name`/`item_price` di `order_items`), jadi kalau harga
  menu berubah nanti, pesanan lama tidak ikut berubah.

## Phase 3 — Dashboard Admin ✅

- `GET /admin/login` — login 1 akun admin/cashier (tabel `users` bawaan
  Laravel). Tidak ada fitur register/lupa password sama sekali.
- Semua route `/admin/*` (kecuali `/admin/login`) dilindungi middleware
  `auth` — akses tanpa login otomatis dilempar ke halaman login.
- `GET /admin/dashboard` — daftar semua meja yang sedang punya order
  `ongoing`: jumlah item, waktu mulai, total. Kosong kalau tidak ada
  meja aktif.
- `GET /admin/orders/{order}` — detail pesanan per meja, dengan tombol:
  - **"Tandai Sudah Dibayar & Bersihkan Meja"** → status jadi `paid`,
    `closed_at` diisi. Meja langsung bebas untuk pelanggan berikutnya
    (dites: order lama tetap ada di database sebagai riwayat, order baru
    yang dibuat pelanggan berikutnya benar-benar order baru/kosong).
  - **"Batalkan Pesanan"** → status jadi `cancelled` (untuk kasus
    pelanggan pergi tanpa bayar).
- Status order (`OrderStatus` enum) ditampilkan dalam Bahasa Indonesia:
  Berjalan / Lunas / Dibatalkan.

## Phase 4 — Admin Kelola Menu (CRUD) ✅

- `GET /admin/menu-items` — daftar semua item menu, dikelompokkan per
  kategori, dengan tombol Nonaktifkan/Aktifkan, Ubah, Hapus.
- **Tambah/ubah item**: nama, kategori, harga, badge Baru/VDT, status
  tersedia, urutan tampil, **upload foto** (JPG/PNG/WebP, maks 2MB).
  Foto tersimpan di `storage/app/public/menu-items/`, diakses lewat
  symlink `public/storage` (`php artisan storage:link`, sudah dijalankan).
- **Guard hapus item**: item yang **sudah pernah dipesan pelanggan**
  (ada baris di `order_items`) **tidak bisa dihapus permanen** — sistem
  menolak dengan pesan jelas, arahkan pakai "Nonaktifkan" saja. Ini
  supaya riwayat pesanan lama tidak rusak (foreign key `restrictOnDelete`).
  Item yang belum pernah dipesan boleh dihapus permanen.
- `GET /admin/categories` — CRUD kategori: nama, urutan, **ikon** (dipilih
  lewat grid kartu visual — `<x-icon-picker>`, bukan dropdown teks).
- **Guard hapus kategori**: kategori yang masih punya item menu di
  dalamnya **tidak bisa dihapus** (mencegah cascade-delete tidak sengaja
  yang akan ikut menghapus semua itemnya). Kategori kosong boleh dihapus.
- Semua perubahan (item baru, nonaktifkan, ganti ikon kategori) langsung
  kelihatan di menu pelanggan tanpa perlu deploy ulang.

## Phase 5 — Polish ✅

- **Halaman error bertema Kafeign** (`resources/views/errors/`):
  - `404.blade.php` — nomor meja/URL tidak valid, tombol "Kembali ke
    Beranda". Menggantikan halaman 404 putih polos bawaan Laravel.
  - `419.blade.php` — sesi/CSRF token kedaluwarsa (kasus realistis:
    pelanggan buka tab lama-lama lalu baru submit), tombol "Muat Ulang".
  - `500.blade.php` — kesalahan server umum. Cuma aktif kalau
    `APP_DEBUG=false` (produksi); saat development `APP_DEBUG=true` jadi
    tetap muncul halaman debug Laravel seperti biasa.
- **Hapus item dari pesanan berjalan** — tiap baris di halaman "Pesanan
  Kamu" (`/table/{nomor}/order`) punya tombol hapus (ikon ×).
  `DELETE /table/{nomor}/order-items/{orderItem}`
  (`OrderController::destroy`):
  - Dilindungi 2 guard: item itu harus **milik meja yang sedang diakses**
    (dites: coba hapus lewat URL meja lain → 404, item tidak ikut
    terhapus), dan order-nya harus masih **`ongoing`** (tidak bisa ubah
    tab yang sudah lunas/dibatalkan).
  - Kalau item yang dihapus adalah **item terakhir** di tab itu, seluruh
    order ikut dihapus (bukan dibiarkan jadi tab kosong) — meja langsung
    bebas lagi, pelanggan diarahkan balik ke menu dengan pesan konfirmasi.
- **Feedback visual tombol tambah pesanan** — tombol "+" di menu sekarang
  punya 3 state (idle → loading [spinner] → sukses [centang] → idle
  lagi setelah ~0.7 detik), bukan cuma disable diam-diam seperti
  sebelumnya. Dites eksplisit ketiga state-nya muncul berurutan.
