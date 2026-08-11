# Fitur yang Sudah Jalan

Status per fase pengerjaan. Semua yang ditandai ✅ sudah diverifikasi
langsung (browser + database + kadang `tinker`/curl), bukan cuma ditulis
kodenya. Kalau menemukan sesuatu di sini yang ternyata sudah tidak akurat
(kode berubah tapi dokumen ini lupa di-update), percaya kodenya, lalu
tolong perbaiki file ini.

---

## Phase 0 — Database & Data Asli ✅

- SQLite di `database/database.sqlite`, dengan migration inti untuk
  `categories`, `menu_items`, `tables`, `orders`, `order_items`.
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

## Phase 6 — Fondasi: server bisa bicara ke pelanggan ✅

Tidak banyak yang terlihat, tapi dua cacat tersembunyi di bawah ini akan
membuat semua fase setelahnya percuma kalau tidak diperbaiki duluan.

- **Server tidak pernah bisa mengirim pesan error ke pelanggan.**
  `bootstrap/app.php` memakai
  `shouldRenderJsonWhen(fn ($r) => $r->is('api/*'))`. Callback itu
  **menggantikan**, bukan menambah, pengecekan bawaan Laravel — dan
  aplikasi ini tidak punya route `api/*` sama sekali. Jadi setiap
  `fetch()` dapat HTML atau 302, tidak pernah JSON: validasi gagal datang
  sebagai redirect, 403/419/429 sebagai halaman HTML. **Pesan apa pun yang
  ditulis untuk pelanggan mustahil sampai** — memperbaiki JS saja tidak
  akan mengubah apa pun. Sekarang `expectsJson()` ikut di-OR.
- **Konfigurasi SQLite akan gagal justru saat transaksi uang bersamaan.**
  `transaction_mode` DEFERRED + `busy_timeout` kosong; pola "cek stok lalu
  kurangi" adalah baca-lalu-tulis, dan penulis kedua langsung dapat
  "database is locked". Sekarang IMMEDIATE + timeout 5 detik + WAL.
  Tercatat juga di komentar config: **`lockForUpdate()` tidak berfungsi
  sama sekali di SQLite** — pengamannya adalah unique index + catch.
- **Sistem toast** (`components/toast-host.blade.php` + `resources/js/toast.js`):
  flash server dirender sebagai HTML asli (tetap jalan tanpa JS), dan JS
  meng-**clone `<template>`** alih-alih merakit markup — ini bukan gaya,
  tapi keharusan: Tailwind v4 memindai file sumber, jadi class yang
  dirakit runtime akan terpurge dan toast-nya tampil tanpa gaya di
  produksi. Pakai `textContent`, bukan `innerHTML`. Tanpa emoji; varian
  dibedakan warna + kata **Berhasil / Gagal / Perhatian / Info**.
- **`postJson()` di `app.js`** — satu jalur untuk semua fetch pelanggan,
  menangani 2xx/422/403/419/429/5xx. Pesan **429 di-hardcode dalam Bahasa
  Indonesia** karena bawaan Laravel berbahasa Inggris ("Too Many
  Attempts" — sudah diverifikasi server memang mengirim itu). 403 diberi
  tombol "Muat Ulang" karena penolakan itu memang pulih dengan reload.
- **Bug UI nyata**: form admin menulis "maks 2MB" padahal server menerima
  10MB — penyebab upload foto gagal yang dilaporkan pemilik. Label dan
  aturan validasi sekarang sama-sama membaca
  `config('kafeign.menu_photo.max_kb')`.

## Phase 7 — Keranjang (pesanan tidak lagi langsung ke kasir) ✅

Ini perubahan alur terbesar sejak Phase 2, atas permintaan pemilik: satu
salah tap tidak boleh langsung jadi pesanan sungguhan.

**Alur baru:** tap "+" → masuk **keranjang** (privat, kasir tidak lihat) →
pelanggan review di `/table/{n}/cart` → tekan **"Kirim Pesanan ke Kasir"**
+ dialog konfirmasi → **baru** jadi order yang terlihat kasir.

- **Keranjang disimpan di session server** (`App\Support\Cart`), isinya
  **hanya `menu_item_id => quantity`** — tidak pernah nama atau harga.
  Keduanya dilihat ulang dari database saat render dan saat submit,
  sehingga klien tidak bisa menentukan harganya sendiri dan keranjang
  tidak pernah menampilkan harga basi.
- **Beberapa HP di satu meja masing-masing punya keranjang sendiri**
  (session bersifat per-browser), tapi semuanya masuk ke **satu tab
  meja yang sama**. Terverifikasi lewat dua sesi browser terpisah.
- **Bisa kirim berkali-kali** — gelombang kedua masuk ke tab yang sama,
  dan item yang sama digabung jadi satu baris (Matcha x3, bukan 3 baris).
- **Submit bersifat semua-atau-tidak sama sekali**, dalam satu transaksi
  database (`App\Services\CartSubmitter`). Sudah disiapkan titik sambung
  eksplisit untuk pengecekan stok Phase 8.
- Route lama `POST /table/{n}/order-items` beserta `AddOrderItemRequest`
  **dihapus** — satu-satunya jalan item masuk tab sekarang lewat keranjang.

**Membedakan keranjang vs tagihan itu wajib, bukan hiasan.** Keranjang
tidak terlihat kafe; kalau pelanggan tertukar, dia menunggu makanan yang
tidak pernah dibuat siapa pun. Karena itu:
- Bar bawah keranjang **amber bergaris putus-putus** bertuliskan
  "Keranjang · belum dikirim"; bar tagihan **maroon solid** bertuliskan
  "Tagihan meja · sudah di kasir". Tidak pernah muncul bersamaan.
- Halaman keranjang diawali peringatan "Item di bawah **belum dikirim**".
- Halaman pesanan menampilkan notice amber kalau masih ada isi keranjang.
- Dialog konfirmasi menyebut jumlah item, total rupiah, dan bahwa
  pembatalan sendiri hanya bisa beberapa menit setelahnya.

**Konsekuensi yang harus disadari pemilik:** barista sekarang tidak
melihat apa pun sampai pelanggan menekan Kirim, dan keranjang yang tidak
pernah dikirim tidak terlihat staf sama sekali. Itu memang harga dari
perlindungan salah-tap — mitigasinya adalah ketiga penanda visual di atas.

*Diverifikasi manual*: dua sesi browser di meja 7 → keranjang tidak saling
terlihat → **0 order di database sebelum Kirim** (inti fase ini) →
keduanya Kirim → **satu** order berisi keduanya → gelombang kedua masuk ke
tab yang sama → batal di dialog konfirmasi tidak membuat order apa pun →
submit keranjang kosong ditolak.

## Phase 8 — Stok otomatis ✅

Stok per item yang berkurang sendiri saat dipesan, dan item habis tidak
bisa dipesan lagi.

- **Kolom `menu_items.stock` sengaja NULLABLE, dan NULL ≠ 0.**
  NULL berarti **"tidak dilacak"**, 0 berarti **"habis"**. Dua alasan yang
  keduanya nyata: default 0 akan menandai **69 item langsung habis** begitu
  migration jalan, dan latte yang diracik dari bahan curah tidak masuk akal
  dihitung per porsi — memaksanya dihitung menjamin muncul "Habis" palsu di
  minggu pertama. Migration ini **tidak mengubah perilaku apa pun** sampai
  pemilik mengisi angka per item. Cocok untuk barang yang bisa dihitung:
  kue, roti, botolan, snack.
- **Satu gerbang tunggal** untuk "boleh dipesan sekarang", di
  `MenuItem::scopeAvailable()`: `is_available = true AND (stock IS NULL OR
  stock > 0)`. Semua jalur uang sudah memanggilnya, jadi otomatis ikut.
  Scope kedua `scopeListedOnMenu()` khusus **tampilan**.
  **Aturannya: `available()` satu-satunya yang boleh menjaga penulisan;
  `listedOnMenu()` hanya boleh menjaga tampilan.**
- **Item habis tetap tampil**, abu-abu, badge "Habis", tombol nonaktif —
  bukan disembunyikan. Pelanggan yang memegang menu berhak dapat jawaban,
  dan itu mengiklankan item untuk besok. Hanya `is_available = false`
  (saklar admin "tidak dijual hari ini") yang benar-benar menyembunyikan.
  Item yang tinggal ≤5 menampilkan "Sisa N porsi".
- **Pengurangan stok atomik dalam satu statement SQL**
  (`App\Services\StockLedger`, satu-satunya tempat stok ditulis). Ini
  wajib karena `lockForUpdate()` tidak berfungsi di SQLite — baca-lalu-tulis
  akan menyisakan celah untuk dua pelanggan sama-sama lolos dan membuat
  stok minus.
- **Submit semua-atau-tidak sama sekali.** Keranjang berisi 3 Cireng tapi
  sisa 1 → tidak ada yang ditulis, stok tetap 1, order tidak dibuat sama
  sekali, dan error muncul **di baris item yang bermasalah**. Pemenuhan
  sebagian ditolak karena diam-diam mengubah pesanan pelanggan, dan mereka
  baru sadar di kasir — tempat termahal untuk menyadarinya.

**Aturan stok, sekali saja:** *dikurangi tepat sekali saat submit;
dikembalikan hanya saat baris dibatalkan; tidak pernah disentuh saat
pembayaran.*

| Kejadian | Stok kembali? |
|---|---|
| Pelanggan hapus item (dalam 3 menit) | Ya |
| Kasir batalkan pesanan | Ya, **kalau checkbox dicentang** (default ya) |
| Kasir tandai lunas | **Tidak pernah** — barangnya memang terjual |

- **Batas hapus-sendiri 3 menit** (`config('kafeign.order.self_delete_window_minutes')`).
  Lewat itu tombol hapus diganti "Sedang diproses" dan pelanggan diarahkan
  ke kasir. Alasannya: kalau barista sudah membuat minumannya,
  mengembalikan stok membuat kafe kelebihan jual besoknya. Set 0 untuk
  mengembalikan perilaku lama (bebas kapan saja).
- Checkbox **"Kembalikan stok ke menu"** saat kasir membatalkan, default
  tercentang — hanya kasir yang tahu apakah makanannya sudah dibuat.
- **Restock cepat** langsung dari daftar menu admin (mode `add` = delta
  atomik untuk kiriman barang; mode `set` = nilai absolut untuk opname).

*Diverifikasi manual*: migration no-op (69 item tetap bisa dipesan) →
stok 0 tidak bisa dipesan tapi tetap tampil → `is_available=false`
disembunyikan walau stok 50 → stok 1 + keranjang minta 3 → **ditolak,
stok tetap 1, order tidak dibuat, item lain di keranjang juga tidak
ditulis** → dua orang rebutan stok terakhir → yang kedua ditolak, stok
**0 bukan −1** → item NULL tetap NULL setelah dipesan 5 → lunas tidak
mengembalikan stok, batal mengembalikan → restock +10 lewat admin.

**Bug yang ketemu saat verifikasi**: `MenuController` masih memakai
`available()` untuk menampilkan menu, sehingga item habis **hilang total**
alih-alih tampil abu-abu. Persis kesalahan yang aturan dua-scope di atas
dibuat untuk mencegah — sudah diperbaiki ke `listedOnMenu()`.

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
