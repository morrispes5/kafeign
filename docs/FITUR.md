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

---

## Audit Keamanan & Perbaikan ✅

Satu putaran audit menyeluruh terhadap seluruh kode, dengan tiap temuan
dibuktikan lewat serangan/reproduksi nyata, lalu diperbaiki dan diuji
ulang.

| # | Temuan | Status |
|---|---|---|
| 1 | Siapa pun bisa menambah/menghapus/melihat pesanan meja mana pun (terbukti: Rp800.000 masuk ke tab orang asing dalam 1 request) | **Dikurangi drastis** — lihat catatan di bawah |
| 2 | Login admin tanpa batas percobaan (brute force bebas) | **Ditutup** — 5x gagal → terkunci 60 detik |
| 3 | Ubah harga menu ikut mengubah harga pesanan yang sudah masuk (terbukti pelanggan kelebihan bayar Rp7.000) | **Ditutup** — harga lama dipertahankan, unit baru masuk baris sendiri |
| 4 | Admin edit item/kategori + kosongkan kolom "Urutan" → error 500 | **Ditutup** — ternyata kena di 2 controller, dua-duanya diperbaiki |
| 5 | Nama menu ber-apostrof mematikan dialog konfirmasi hapus (hapus tanpa peringatan) + celah injeksi JS | **Ditutup** — pesan konfirmasi jadi data attribute, bukan kode |
| 7 | Tidak ada unique constraint `(order, item, harga)` — dua tap bersamaan bisa bikin baris duplikat | **Ditutup** — unique index + transaksi + penanganan race |
| 6 | Jumlah per item menumpuk tanpa batas (terbukti 1.000 porsi / Rp40 juta) | **Ditutup** — batas 50 unit per item |

**Sudah dicek dan memang aman** (tidak perlu perbaikan): SQL injection
(Eloquent parameterized), CSRF di semua form, upload SVG (ditolak
Laravel 13, ini vektor XSS klasik), endpoint `PUT /storage/{path}`
bawaan Laravel (butuh signature, 403), N+1 query (halaman menu 5 query).

### Catatan jujur soal temuan #1

Belum tertutup 100%, dan itu batasan desain bukan kelalaian: pelanggan
tidak login, beberapa orang satu meja harus bisa pesan dari HP
masing-masing, jadi "membuka halaman meja" adalah satu-satunya bukti
kehadiran yang tersedia. Penyerang yang mau repot membuka halaman meja
korban dulu masih bisa menambah pesanan.

Yang sudah tertutup: serangan buta/otomatis ke banyak meja sekaligus,
dan besar kerusakannya (dibatasi 20 request/menit + 50 unit per item).
Penutupan penuh butuh token per meja di QR — lihat [ROADMAP.md](ROADMAP.md).

---

## Perbaikan Foto Menu ✅

Ditemukan langsung oleh user setelah pakai fitur upload foto di Phase 4
("gambar kecil banget" + "pas upload sendiri error").

| # | Temuan | Status |
|---|---|---|
| 8 | Upload foto dari HP (biasanya 3-8MB) ditolak — batas `upload_max_filesize` di PHP cuma 2M | **Ditutup** — dinaikkan ke 12M, plus foto sekarang di-resize otomatis jadi maks 1000px/JPEG di server (`App\Support\MenuItemImage`), jadi ukuran file tersimpan konsisten kecil apa pun yang di-upload |
| 9 | Foto yang berhasil ter-upload gagal tampil di browser (URL menunjuk port yang salah, dari `APP_URL=http://localhost` tanpa port sementara server jalan di port 8000) | **Ditutup** — `MenuItem::image_url` diganti jadi URL root-relative (`/storage/...`), otomatis ikut origin halaman, tidak bisa lagi salah port |
| 10 | Thumbnail foto di menu pelanggan cuma 48×48px — kebaca kayak ikon, bukan foto makanan | **Ditutup** — diperbesar ke 96×96px dengan border, thumbnail admin ikut diperbesar jadi 64×64px |

Dites end-to-end: upload foto 3,44MB/4000×3000px (simulasi foto HP asli)
lewat form sungguhan → tersimpan 219KB/1000×750px → tampil normal di
menu pelanggan pada ukuran 96×96px.
