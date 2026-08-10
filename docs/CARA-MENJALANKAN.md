# Cara Menjalankan

## Persiapan (sekali saja)

Ekstensi PHP `pdo_sqlite`, `sqlite3`, dan `fileinfo` harus aktif di
`php.ini`. Kalau environment ini yang dipakai (PHP 8.4 di
`C:\php-8.4.13`), itu sudah diaktifkan sejak Phase 0 — cek dengan:

```bash
php -m | grep -i sqlite
```

Kalau project di-clone ke environment baru:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan storage:link
php artisan migrate --seed
```

## Jalankan Sehari-hari

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Biarkan terminal tetap terbuka; `Ctrl+C`
untuk berhenti.

Kalau sedang aktif edit file CSS/JS dan mau lihat perubahan otomatis
tanpa build manual, jalankan di terminal terpisah (boleh bersamaan
dengan `php artisan serve`):

```bash
npm run dev
```

Kalau tidak sedang develop CSS/JS, cukup pastikan asset ter-build sekali:

```bash
npm run build
```

## URL Penting

| URL | Untuk siapa |
|---|---|
| `/` | Pelanggan — pilih meja |
| `/table/{1-36}/menu` | Pelanggan — langsung ke menu meja tertentu |
| `/admin/login` | Admin/kasir |
| `/admin/dashboard` | Admin — setelah login |

## Kredensial Admin

Dibaca dari `.env`:

```
ADMIN_EMAIL=admin@kafeign.test
ADMIN_PASSWORD=change-me-please
```

⚠️ **Ini password contoh/default dari proses development, bukan untuk
dipakai di kafe sungguhan.** Sebelum go-live: ganti nilainya di `.env`,
lalu jalankan `php artisan migrate:fresh --seed` (atau `db:seed
--class=AdminUserSeeder` saja kalau tidak mau reset data lain) supaya
password baru ke-apply.

## Reset Database ke Kondisi Bersih

Berguna setelah testing manual (misal habis coba-coba pesan/hapus data):

```bash
php artisan migrate:fresh --seed
```

Ini **menghapus semua data** (termasuk pesanan) dan mengembalikan ke
seed asli (9 kategori, 69 menu, 36 meja, 1 admin). Foto yang sudah
di-upload admin **tidak ikut terhapus otomatis** (file fisiknya tetap
ada di `storage/app/public/menu-items/`, tapi kolom `image_path` di
database jadi kosong lagi setelah reset) — hapus manual foldernya kalau
mau benar-benar bersih.

## Inspeksi Cepat Lewat Tinker

```bash
php artisan tinker
```

Contoh yang sering dipakai selama development:

```php
// Lihat semua meja yang sedang aktif
App\Models\Order::ongoing()->with('table', 'orderItems')->get();

// Cek invariant "1 order aktif per meja" masih berlaku
App\Models\Order::where('status', 'ongoing')->count();

// Cek 1 item menu
App\Models\MenuItem::where('name', 'Matcha')->first();
```
