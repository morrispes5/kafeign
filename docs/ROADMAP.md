# Roadmap

## Status

Phase 0–9 **sudah selesai** (lihat [FITUR.md](FITUR.md) untuk detail
lengkap tiap fase, termasuk apa yang sudah diverifikasi), ditambah satu
putaran audit keamanan dan perbaikan foto menu. Phase 9 (Pembayaran &
Struk) menutup dua dari tiga catatan "masih terbuka" versi sebelumnya —
lihat perubahan di bawah.

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
ke pengunjung. Ini benar untuk dev lokal (dan sengaja dibiarkan begitu di
`.env.example` — mengubahnya akan merusak pengalaman debug sehari-hari),
tapi **sebelum go-live**: `APP_DEBUG=false` dan `APP_ENV=production` di
`.env` yang sungguhan dipakai server produksi. Lihat checklist go-live di
[CARA-MENJALANKAN.md](CARA-MENJALANKAN.md).

### ~~3. Kredensial admin masih nilai contoh~~ — sudah beres

`ADMIN_PASSWORD` di `.env` lokal sudah bukan `change-me-please` lagi
(dicek ulang saat pengerjaan Phase 9). Tidak ada tindakan lanjutan,
dicatat di sini cuma supaya tidak ada yang mengira ini masih terbuka.

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

- **Dashboard live update — sudah ditandai "Phase 10" di kode** (komentar
  di `Order::$last_item_added_at`, kolom yang sudah diisi tiap kali
  keranjang di-submit tapi belum dibaca siapa pun). Saat ini admin harus
  klik "Muat Ulang" manual untuk lihat meja baru aktif. Bisa ditingkatkan
  pakai polling interval sederhana (`config('kafeign.dashboard.poll_seconds')`
  — sudah disiapkan sejak Phase 6, belum dipakai) atau Laravel Echo/
  WebSocket kalau mau benar-benar real-time.
- **Laporan penjualan** — sekarang lebih mudah dari sebelumnya: Phase 9
  menambah `orders.business_date`, `payment_method`, dan `total_frozen`,
  jadi laporan harian/mingguan per metode bayar tinggal `GROUP BY`,
  tidak perlu ubah skema atau hitung ulang apa pun. Halaman Riwayat
  (`/admin/orders`) sudah ada tapi sengaja tanpa agregasi — ini beda hal.
- **Multi-admin dengan role berbeda** — saat ini cuma 1 akun admin generik.
  Kalau perlu bedakan kasir vs pemilik, perlu tambah kolom role ke `users`.
- **Riwayat pesanan per meja untuk pelanggan** — pelanggan saat ini cuma
  lihat tab yang sedang berjalan, tidak ada riwayat kunjungan sebelumnya
  (memang tidak ada konsep akun pelanggan sama sekali, sesuai desain awal).
  Beda dari halaman Riwayat Phase 9, yang untuk admin/kasir, bukan
  pelanggan.
- **Ukuran kertas struk untuk printer thermal** — halaman
  `admin/receipt.blade.php` (Phase 9) memakai lebar standar browser-print;
  belum ada CSS `@page` khusus 58mm/80mm karena belum diketahui printer
  apa yang akan dipakai kafe.
