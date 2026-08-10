# Roadmap

## Status

Semua fase dari plan awal (Phase 0–5) **sudah selesai**, ditambah satu
putaran audit keamanan. Lihat [FITUR.md](FITUR.md) untuk detail lengkap
tiap fase termasuk apa yang sudah diverifikasi.

## Yang masih terbuka (dari audit)

### 1. Token per meja di QR code — satu-satunya penutup celah lintas meja

Prioritas tertinggi kalau website ini benar-benar dipasang di kafe dan
bisa diakses dari internet.

Saat ini nomor meja bisa ditebak (`/table/1` … `/table/36`), dan
`EnsureTableSession` hanya mensyaratkan penyerang membuka halaman meja
itu dulu — hambatan, bukan tembok (penjelasan lengkap di
[ARSITEKTUR.md](ARSITEKTUR.md#-batas-yang-harus-dipahami)).

Rancangan yang menutup penuh:
- Tambah kolom `access_token` (acak, panjang) di tabel `tables`.
- QR tiap meja memuat `/table/{nomor}?t={token}`; token valid → sesi
  browser itu ditandai sah untuk meja tersebut.
- **Konsekuensi yang harus diputuskan pemilik**: dropdown "pilih nomor
  meja manual" di halaman depan jadi bypass token, jadi harus dihapus
  atau diganti PIN pendek yang dicetak di tent card meja. Ini mengubah
  UX yang sekarang, makanya belum dikerjakan sepihak.

### 2. `APP_DEBUG=true` di `.env.example`

Bawaan Laravel, dan panduan menyuruh `cp .env.example .env`. Kalau
dideploy apa adanya, setiap error menampilkan stack trace + path server
ke pengunjung. Sebelum go-live: `APP_DEBUG=false` dan `APP_ENV=production`.

### 3. Kredensial admin masih nilai contoh

`ADMIN_PASSWORD=change-me-please` di `.env` lokal. Wajib diganti sebelum
dipakai staf sungguhan.

## Yang Sengaja TIDAK Dikerjakan (di luar scope awal)

Supaya tidak ada yang mengira ini "lupa dikerjakan" — ini memang di luar
permintaan awal user:

- **Generate QR code** — user eksplisit bilang "untuk QR nya jangan
  dipikirkan, yang penting website nya ada dulu". Website siap dipasangi
  QR code (tinggal generate QR yang mengarah ke `/table/{nomor}`), tapi
  proses generate QR-nya sendiri tidak dibangun di sini.
- **Payment gateway / pembayaran online** — pembayaran memang didesain
  offline di kasir, sesuai business flow yang dijelaskan user.
- **Halaman Booking/About/Contact** — ada di `legacy-static-site/` lama,
  tapi tidak dibawa ke Laravel karena bukan bagian dari alur pemesanan
  berbasis meja yang diminta.
- **Multi-cabang / multi-cafe** — skema database saat ini mengasumsikan
  1 kafe dengan 36 meja tetap.

## Ide Pengembangan Lanjutan (belum diminta, sekadar catatan)

Kalau suatu saat user mau lanjut lebih jauh dari plan awal, beberapa ide
yang masuk akal secara arsitektur (karena fondasinya sudah cocok):

- **Laporan penjualan** — data `orders`/`order_items` historis (status
  `paid`) sudah cukup untuk bikin laporan harian/mingguan, tinggal
  ditambah halaman admin baru, tidak perlu ubah skema.
- **Dashboard live update** — saat ini admin harus klik "Muat Ulang"
  manual untuk lihat meja baru aktif. Bisa ditingkatkan pakai polling
  interval sederhana atau Laravel Echo/WebSocket kalau mau real-time.
- **Cetak struk** — halaman `admin/order-detail.blade.php` sudah punya
  semua data yang dibutuhkan (rincian item, total), tinggal tambah CSS
  print-friendly atau generate PDF.
- **Multi-admin dengan role berbeda** — saat ini cuma 1 akun admin generik.
  Kalau perlu bedakan kasir vs pemilik, perlu tambah kolom role ke `users`.
- **Riwayat pesanan per meja untuk pelanggan** — pelanggan saat ini cuma
  lihat tab yang sedang berjalan, tidak ada riwayat kunjungan sebelumnya
  (memang tidak ada konsep akun pelanggan sama sekali, sesuai desain awal).
