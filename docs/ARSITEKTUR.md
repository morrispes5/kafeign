# Arsitektur

## Skema Database

SQLite. Tabel milik project ini: `categories`, `menu_items`, `tables`,
`orders`, `order_items` — di luar tabel bawaan Laravel (`users`, `cache`,
`jobs`, `sessions`). `users` dipakai apa adanya untuk akun admin.

```
categories (1) ──< menu_items (M)
tables (1) ──< orders (M)          [hanya 1 boleh status='ongoing' per meja]
orders (1) ──< order_items (M)
menu_items (1) ──< order_items (M) [FK dipertahankan untuk laporan; nama/harga di-snapshot]
users                               [1 baris admin, tidak ada relasi ke order]
```

### `categories`
`name`, `slug` (unik), `icon` (key ke set ikon tetap di `<x-icon>`,
lihat `App\Models\Category::ICONS`), `sort_order`.

### `menu_items`
`category_id` (FK, cascade delete — lihat catatan bahaya di bawah),
`name`, `price` (integer Rupiah, contoh `23000` = Rp 23.000, **tanpa
desimal**), `image_path` (nullable, diisi lewat upload admin),
`is_new`, `is_vdt` (boolean, badge), `is_available` (boolean — status
"nonaktif" tanpa perlu hapus data), `sort_order`.

### `tables`
Cuma `number` (1–36, unik). Model Eloquent-nya sengaja dinamai `Table`
(bukan `DiningTable`) — kalau baca kode dan lihat `App\Models\Table`,
itu meja fisik kafe, bukan istilah "database table".

### `orders`
Satu baris = satu tab/tagihan berjalan untuk satu meja, dari item
pertama dipesan sampai kasir membersihkannya.
- `dining_table_id` (FK ke `tables`)
- `status` — string di kolom, di-cast ke enum `App\Enums\OrderStatus`
  (`Ongoing` / `Paid` / `Cancelled`) di level model
- `opened_at`, `closed_at` (nullable)
- **Total TIDAK disimpan** sebagai kolom — dihitung on-the-fly lewat
  accessor `Order::total` (`sum` dari `order_items.subtotal`), supaya
  tidak mungkin "nyasar" beda dari jumlah item sebenarnya.

**Invariant inti** — satu meja hanya boleh punya satu order `ongoing`:
ditegakkan lewat **partial unique index** di migration `orders`
(`create_orders_table`):
```sql
CREATE UNIQUE INDEX unique_ongoing_order_per_table
ON orders (dining_table_id) WHERE status = 'ongoing';
```
Ini jaminan di level database, bukan cuma dicek di kode — kalau ada race
condition (dua request nyaris bersamaan), yang kalah akan gagal insert
dan `Order::findOrCreateOngoingForTable()` (di `App\Models\Order`)
menangkap itu lalu query ulang, bukan bikin duplikat.

### `order_items`
Satu baris = satu jenis item **pada satu harga** di dalam sebuah tab.
- `order_id`, `menu_item_id` (FK, **restrict on delete** — lihat catatan
  bahaya di bawah)
- `item_name`, `item_price` — **snapshot** nama & harga menu item *saat
  dipesan*, bukan referensi live ke `menu_items`. Kalau harga menu
  berubah, pesanan yang sudah tercatat tidak ikut berubah.
- `quantity`, `subtotal` (`item_price * quantity`, dihitung otomatis
  lewat model event `saving` di `App\Models\OrderItem` — bukan
  `creating`, supaya ikut ke-update saat quantity di-top-up, bukan cuma
  saat baris pertama dibuat)

**Unique index `order_items_unique_line` pada `(order_id, menu_item_id,
item_price)`** — ini yang menegakkan dua jaminan sekaligus:

1. Pesan item yang sama berulang kali **digabung** jadi satu baris
   ("Matcha x3"), dan tidak bisa pecah jadi baris duplikat gara-gara dua
   tap nyaris bersamaan (dulu `firstOrNew` lalu `save` adalah pola
   read-then-write yang rawan race).
2. `item_price` sengaja ikut jadi kunci. Kalau admin mengubah harga di
   tengah kunjungan, unit yang sudah terlanjur dipesan **tetap di harga
   lama**, dan unit baru masuk ke baris sendiri di harga baru. Sebelum
   ini diperbaiki, `addItem()` menimpa snapshot harga lama sehingga
   pelanggan bisa tertagih harga baru untuk minuman yang sudah dia pesan
   satu jam sebelumnya.

### ⚠️ Dua foreign key yang sengaja "berbahaya" (jangan diubah tanpa paham konsekuensinya)

1. `menu_items.category_id` → `categories.id` **cascadeOnDelete**.
   Menghapus kategori akan ikut menghapus SEMUA item di dalamnya. Admin
   UI (`Admin\CategoryController::destroy`) memblokir penghapusan
   kategori yang masih punya item — itu satu-satunya pengaman. Kalau
   ada kode baru yang menghapus `Category` langsung (misal lewat
   `tinker` atau seeder), pengaman itu tidak berlaku.
2. `order_items.menu_item_id` → `menu_items.id` **restrictOnDelete**.
   Item menu yang sudah pernah dipesan (ada baris `order_items`) tidak
   bisa dihapus — database akan menolak. `Admin\MenuItemController::destroy`
   mengecek ini duluan dan kasih pesan ramah; tanpa pengecekan itu,
   penghapusan akan gagal dengan `QueryException` mentah.

## Keamanan

Pelanggan tidak pernah login — nomor meja adalah satu-satunya identitas
yang dipunya aplikasi ini. Itu konsekuensi dari desain QR-nya, dan
membuat endpoint pemesanan jadi permukaan serang utama.

| Lapisan | Di mana | Melindungi dari |
|---|---|---|
| `EnsureTableSession` middleware | `app/Http/Middleware/` | Orang luar POST/DELETE ke tab meja yang tidak pernah dia buka |
| `throttle:20,1` | grup route order-items | Skrip yang membanjiri pesanan |
| `Order::MAX_QUANTITY_PER_ITEM` (50) | dicek di `OrderController::store` | Satu item ditumpuk sampai jutaan rupiah |
| `LoginRequest` rate limiter (5x / 60 detik) | `app/Http/Requests/Admin/` | Tebak-tebakan password admin |
| `auth` middleware | grup route `/admin/*` | Akses dashboard tanpa login |

### ⚠️ Batas yang harus dipahami

`EnsureTableSession` memberi akses berdasarkan **membuka halaman meja
itu**. Beberapa orang satu meja masing-masing pakai HP sendiri harus
tetap bisa pesan, dan tidak ada rahasia apa pun yang membedakan mereka
dari orang lain yang bisa membuka URL yang sama.

Artinya: penyerang yang **mau repot membuka `/table/{n}/menu` dulu**
masih bisa menambah pesanan ke meja itu. Yang sudah tertutup adalah
serangan buta/otomatis (kirim POST langsung ke 36 meja sekaligus), dan
kerusakan maksimalnya dibatasi rate limit + batas jumlah per item.

Menutup celah ini sepenuhnya butuh **rahasia per meja** yang hanya
diketahui orang yang benar-benar duduk di situ — token acak yang
ditanam di QR code tiap meja. Itu keputusan produk (mengubah isi QR yang
dicetak, dan dropdown "pilih meja manual" perlu diganti/diberi PIN),
bukan sesuatu yang boleh diselipkan diam-diam. Lihat [ROADMAP.md](ROADMAP.md).

## Foto Menu (`app/Support/MenuItemImage.php`)

Foto yang di-upload admin **tidak** disimpan apa adanya. `MenuItemImage::store()`
membaca file lewat GD, mengecilkan sisi terpanjangnya ke maksimal 1000px
(tidak pernah memperbesar foto yang sudah kecil), lalu menyimpannya
sebagai JPEG kualitas 82 — apa pun format aslinya (PNG/WebP/GIF/BMP).

Alasannya: foto kamera HP biasa 3000×4000px dan beberapa MB — jauh lebih
besar dari yang pernah ditampilkan di kartu menu manapun. Tanpa ini,
setiap pelanggan yang buka menu lewat wifi kafe menunggu unduhan berat
untuk gambar yang toh ditampilkan kecil.

**Dua bug yang pernah terjadi di area ini, sudah diperbaiki:**
1. `upload_max_filesize` PHP sempat 2M — lebih kecil dari foto HP biasa,
   jadi upload ditolak duluan sebelum sempat divalidasi Laravel. Sudah
   dinaikkan (`php.ini`: `upload_max_filesize=12M`, `post_max_size=16M`),
   dan validasi `image` di form request ikut dinaikkan ke `max:10240` (KB).
2. `MenuItem::image_url` sebelumnya pakai `Storage::disk('public')->url()`,
   yang membangun URL dari `APP_URL` di `.env` — dan `APP_URL` default-nya
   `http://localhost` **tanpa port**, sementara `php artisan serve`
   jalan di port 8000. Hasilnya: foto ter-upload dan ter-resize dengan
   benar di server, tapi **gagal dimuat di browser** karena URL-nya
   menunjuk ke port yang salah. Diperbaiki dengan URL root-relative
   (`/storage/...`, tanpa host/port sama sekali) yang otomatis mengikuti
   origin halaman yang sedang dibuka — tidak bisa lagi berbeda dari
   `APP_URL`.

## Model Eloquent (`app/Models/`)

| Model | Hal penting |
|---|---|
| `Category` | `ICONS` constant (daftar ikon valid), scope `orderedBySort()` |
| `MenuItem` | scope `available()`, accessor `image_url` (null kalau belum ada foto, root-relative `/storage/...` — lihat catatan gambar di bawah) |
| `Table` | `getRouteKeyName()` = `number` (jadi URL pakai nomor meja, bukan id), method `activeOrder()` |
| `Order` | enum-cast `status`, accessor `total` (dihitung, bukan disimpan), `findOrCreateOngoingForTable()`, `addItem()` (merge-by-quantity) |
| `OrderItem` | auto-hitung `subtotal` lewat model event |
| `User` | bawaan Laravel, dipakai untuk admin (bukan tabel terpisah) |

## Routes (`routes/web.php`)

### Sisi Pelanggan (tanpa login)

**Seluruh grup `/table/{nomor}` memakai middleware `EnsureTableSession`**;
yang menulis (POST/PATCH/DELETE) juga memakai `throttle:20,1`.

| Method | URL | Fungsi | Middleware tambahan |
|---|---|---|---|
| GET | `/` | Landing page, dropdown pilih meja | — |
| POST | `/table` | Submit dropdown → redirect ke `/table/{nomor}` | — |
| GET | `/table/{nomor}` | Cek order ongoing → redirect ke menu atau ke pesanan | `EnsureTableSession` |
| GET | `/table/{nomor}/menu` | Browse menu | `EnsureTableSession` |
| GET | `/table/{nomor}/cart` | Lihat & ubah keranjang (belum dikirim) | `EnsureTableSession` |
| GET | `/table/{nomor}/order` | Lihat tab berjalan (sudah di kasir) | `EnsureTableSession` |
| POST | `/table/{nomor}/cart/items` | AJAX tambah item ke **keranjang** | + `throttle:20,1` |
| PATCH | `/table/{nomor}/cart/items` | AJAX ubah jumlah (0 = hapus baris) | + `throttle:20,1` |
| DELETE | `/table/{nomor}/cart/items/{menuItem}` | Hapus baris keranjang | + `throttle:20,1` |
| POST | `/table/{nomor}/cart/submit` | **Kirim keranjang → jadi order nyata** | + `throttle:20,1` |
| DELETE | `/table/{nomor}/order-items/{orderItem}` | Hapus item dari tab yang sudah dikirim | + `throttle:20,1` |

Catatan: `POST /table/{nomor}/order-items` **sudah dihapus** (Phase 7).
Satu-satunya jalan item masuk ke tab sekarang lewat `cart/submit`.

### Sisi Admin (butuh login, middleware `auth`)
| Method | URL | Fungsi |
|---|---|---|
| GET | `/admin` | Redirect: ke dashboard kalau sudah login, ke login kalau belum |
| GET/POST | `/admin/login` | Login (rate limit 5x/60 detik, lihat `Admin\LoginRequest`) |
| POST | `/admin/logout` | Logout |
| GET | `/admin/dashboard` | Daftar meja aktif |
| GET | `/admin/orders/{order}` | Detail + aksi bersihkan/batalkan |
| POST | `/admin/orders/{order}/clear` | Tandai lunas |
| POST | `/admin/orders/{order}/cancel` | Batalkan |
| resource | `/admin/menu-items` | CRUD item menu (`except('show')`) |
| PATCH | `/admin/menu-items/{menuItem}/toggle-availability` | Aktif/nonaktif cepat |
| resource | `/admin/categories` | CRUD kategori (`except('show')`) |

Catatan teknis: route resource `menu-items` di-override parameter
name-nya jadi `menuItem` (bukan default `menu_item`) lewat
`->parameters(['menu-items' => 'menuItem'])`, supaya cocok dengan nama
argumen di controller — kalau lupa, route model binding diam-diam gagal.

## Stok (`App\Services\StockLedger`) — Phase 8

`menu_items.stock` **nullable**, dan **NULL bukan 0**:

| Nilai | Artinya |
|---|---|
| `NULL` | Tidak dilacak — latte racikan, apa pun yang tidak masuk akal dihitung per porsi |
| `0` | Habis — tetap tampil di menu, abu-abu, tidak bisa dipesan |
| `> 0` | Sisa sekian |

### Satu gerbang, dua scope — jangan pernah dibalik

```
available()      = is_available AND (stock IS NULL OR stock > 0)   -> MENJAGA PENULISAN
listedOnMenu()   = is_available                                     -> MENJAGA TAMPILAN
```

**`available()` adalah satu-satunya scope yang boleh menjaga penulisan.**
Kalau kondisi ini ditulis ulang di tempat lain (mis. `Rule::exists(...)
->where('is_available', true)` di sebuah FormRequest), salinan itu akan
diam-diam mempertahankan aturan lama saat aturannya berubah.

`listedOnMenu()` **hanya** untuk memilih apa yang tampil. Item habis harus
tetap muncul (abu-abu + badge "Habis"), karena pelanggan yang memegang
menu berhak dapat jawaban. *Kesalahan ini benar-benar terjadi saat Phase 8
dikerjakan: `MenuController` memakai `available()` untuk menampilkan, dan
item habis hilang total dari menu.*

### Pengurangan harus SATU statement

`lockForUpdate()` tidak berfungsi di SQLite, jadi baca-lalu-tulis
menyisakan celah untuk dua pelanggan sama-sama lolos:

```php
MenuItem::whereKey($id)
    ->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>=', $qty))
    ->decrement('stock', $qty);   // 0 baris terpengaruh = kalah balapan
```

Baris NULL cocok lewat cabang `whereNull`, dan `NULL - n` tetap NULL —
item tak terlacak aman tanpa kondisi khusus. `unsignedInteger` **tidak
membantu** di SQLite (tidak ada tipe unsigned; -3 akan diterima); yang
benar-benar mencegah minus adalah `WHERE` bersyarat di atas.

### Aturan pergerakan stok

> Dikurangi tepat sekali saat submit. Ditambah hanya saat sebuah baris
> dibatalkan. **Tidak pernah** disentuh saat pembayaran.

Pelanggan hapus baris (dalam `config('kafeign.order.self_delete_window_minutes')`,
default 3 menit) → kembali. Kasir batalkan → kembali kalau checkbox
dicentang (hanya kasir yang tahu apakah makanannya sudah dibuat). Kasir
tandai lunas → tidak pernah, barangnya terjual.

Tidak ada tabel `stock_movements` di MVP. Konsekuensi jujurnya: kalau
angkanya melenceng, tidak ada cara menelusuri sebabnya. Semua pergerakan
**otomatis** masih bisa direkonstruksi dari `order_items` + status order +
timestamp; hanya restock manual yang tidak.

## Keranjang (`App\Support\Cart`) — Phase 7

Tap "+" tidak lagi langsung membuat order. Item masuk **keranjang** dulu,
yang privat untuk browser itu dan **tidak terlihat kasir**, sampai
pelanggan menekan "Kirim Pesanan".

```
session('kafeign.cart') = [ 7 => [12 => 2, 45 => 1] ]
                            ^nomor meja  ^menu_item_id => qty
```

**Isinya hanya id dan jumlah — tidak pernah nama atau harga.** Keduanya
dilihat ulang dari `menu_items` saat render dan lagi saat submit. Ini
mematikan dua kelas masalah sekaligus: klien tidak bisa menentukan
harganya sendiri, dan keranjang tidak mungkin menampilkan harga basi.

Kenapa session, bukan tabel `draft`: beberapa orang satu meja pesan dari
HP masing-masing, jadi tiap orang butuh keranjang privat tapi semuanya
harus mendarat di **satu order yang sama** untuk meja itu. Session
bersifat per-browser, jadi itu didapat gratis. Status `draft` justru akan
butuh kolom session-id, butuh pembersihan draft terlantar, dan membuat
setiap `Order::query()` di masa depan jadi ranjau — satu filter status
terlupa, draft bocor ke laporan.

Dikunci per nomor meja karena `EnsureTableSession` sengaja membolehkan
satu session memegang beberapa meja; tanpa kunci itu, rombongan yang
pindah meja akan menyeret keranjangnya.

**Trade-off yang diterima sadar:** keranjang ikut mati bersama session
cookie, dan **staf tidak bisa melihatnya**. Keranjang yang tidak pernah
dikirim tidak terlihat kafe sama sekali — itu harga langsung dari
perlindungan salah-tap. Mitigasinya murni UI (bar amber putus-putus,
peringatan di halaman keranjang, notice di halaman pesanan).

`App\Services\CartSubmitter::submit()` mengubahnya jadi order nyata:
memvalidasi semua baris **sebelum** membuka transaksi (supaya pelanggan
dapat daftar lengkap yang perlu diperbaiki sekaligus), lalu satu
`DB::transaction` — **semua-atau-tidak sama sekali**. Di dalam loop ada
titik sambung eksplisit untuk pengecekan stok Phase 8.

## Views (`resources/views/`)

- `layouts/app.blade.php` — shell pelanggan (header, dark mode, sidebar)
- `layouts/admin.blade.php` — shell admin (nav Dashboard/Menu/Kategori/Keluar)
- `table/` — entry, menu, order (pelanggan)
- `admin/` — login, dashboard, order-detail, menu-items/*, categories/*
- `components/` — `icon`, `icon-picker`, `badge-pill`, `menu-item-card`,
  `order-summary` (Blade components, dipakai lewat `<x-nama-file>`)

## Frontend: Tailwind v4 + vanilla JS

- Tailwind v4 pakai **CSS-first config** (bukan `tailwind.config.js`) —
  semua token warna/font didefinisikan di `@theme` block dalam
  `resources/css/app.css` (prefix `--color-kafeign-*`, `--font-display`).
- Dark mode **class-based** (bukan cuma `prefers-color-scheme`), diatur
  lewat `@custom-variant dark (&:where(.dark, .dark *));` di CSS, dan
  class `dark` di-toggle di `<html>` lewat `resources/js/app.js`.
- Tidak ada JS framework (React/Vue/Livewire/Alpine) — semua interaksi
  (tambah pesanan, dark mode, sidebar) pakai vanilla JS + `fetch()`,
  konsisten dengan gaya `legacy-static-site/js/main.js` yang lama.

## File Kritis (paling penting kalau mau ubah sesuatu)

1. `database/migrations/*_create_orders_table.php` — partial unique index
2. `app/Models/Order.php` — `findOrCreateOngoingForTable()`, `addItem()`
3. `database/seeders/MenuItemSeeder.php` — semua 69 item menu asli
4. `app/Models/Category.php` — `ICONS` constant (harus sinkron dengan
   `resources/views/components/icon.blade.php`)
