# Cara Menjalankan

## Persiapan (sekali saja)

Ekstensi PHP `pdo_sqlite`, `sqlite3`, `fileinfo`, dan `gd` harus aktif di
`php.ini` (`gd` dipakai untuk resize foto menu — lihat bagian "Foto Menu"
di [ARSITEKTUR.md](ARSITEKTUR.md)).
Kalau environment ini yang dipakai (PHP 8.4 di `C:\php-8.4.13`), semua
sudah diaktifkan — cek dengan:

```bash
php -m | grep -iE "sqlite|gd"
```

`upload_max_filesize` dan `post_max_size` juga sudah dinaikkan ke 12M/16M
di `php.ini` supaya foto dari HP (biasanya beberapa MB) tidak ditolak
sebelum sempat diproses. Kalau di-setup ulang di environment lain, ini
perlu disesuaikan juga.

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

## Login Admin

Masuk lewat **`/admin`** (otomatis diarahkan ke halaman login, atau
langsung ke dashboard kalau sudah login). Bookmark alamat itu — tidak
perlu mengetik `/admin/login` lengkap.

Centang **"Tetap masuk di perangkat ini"** (default sudah tercentang)
supaya tidak logout sendiri di tengah shift.

### Kelola akun admin — tidak perlu tinker

| Perintah | Gunanya |
|---|---|
| `php artisan admin:list` | Lihat semua akun yang bisa login + peringatan mana yang belum aman dari reset DB |
| `php artisan admin:create` | Buat akun baru **atau** ganti password akun lama (tinggal pakai email yang sama). Password diketik aman, tidak kelihatan. |
| `php artisan admin:unlock --all` | Buka kunci kalau salah password 5x dan kena "Terlalu banyak percobaan" |

### ⚠️ Yang WAJIB dipahami soal akun admin

Akun admin dibuat ulang dari `.env` setiap kali `migrate:fresh --seed`
dijalankan. Artinya:

- Akun yang **tercatat di `.env`** (`ADMIN_EMAIL`/`ADMIN_PASSWORD`) aman —
  selalu dibuat ulang otomatis.
- Akun yang dibuat **di luar `.env`** (lewat `admin:create` dengan email
  berbeda, atau lewat tinker) ada **hanya di database** — dan akan
  **HILANG** begitu database di-reset.

Ini pernah benar-benar terjadi: akun dibuat lewat tinker, lalu hilang
saat database di-reset waktu testing, dan pemiliknya tiba-tiba tidak
bisa login. `php artisan admin:list` sekarang menandai akun mana yang
berisiko, supaya kejadian itu tidak terulang.

Ganti password admin sebelum dipakai di kafe sungguhan:

```bash
php artisan admin:create
```

lalu samakan `ADMIN_EMAIL`/`ADMIN_PASSWORD` di `.env` dengan akun itu.

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
