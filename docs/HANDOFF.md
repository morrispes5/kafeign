# Handoff — Kafeign

Dokumen ini ada karena pemilik project akan **pindah dari Claude Code ke
tool/AI lain**. Isinya bukan ulangan dari dokumen lain — tujuannya
spesifik: supaya AI atau developer manapun yang masuk ke project ini
selanjutnya (Claude, GPT, Cursor, manusia, siapa pun) bisa langsung tahu
**apa yang belum selesai** dan **apa yang tidak boleh diputuskan sendiri
tanpa tanya pemilik dulu**, tanpa harus membaca ulang seluruh riwayat
percakapan yang tidak akan ikut pindah.

Ditulis 2026-09-05, diverifikasi langsung ke kode + database saat itu
(bukan cuma disalin dari ingatan sesi sebelumnya) — kalau kamu baca ini
jauh setelah tanggal itu dan ada yang sudah tidak akurat, percaya kode
dan `git log`-nya, lalu tolong perbaiki dokumen ini juga.

## 0. Baca dulu, urutannya penting

1. [README.md](../README.md) (root) — ringkasan super singkat + quick start.
2. [docs/README.md](README.md) — konteks bisnis lengkap: ini web apa,
   alurnya gimana, keputusan desain yang jangan diubah sepihak.
3. [docs/FITUR.md](FITUR.md) — **fitur yang SUDAH jalan**, per fase
   (Phase 0–9). Jangan asumsikan sesuatu belum ada tanpa cek di sini +
   kodenya dulu.
4. [docs/ARSITEKTUR.md](ARSITEKTUR.md) — skema database, model, routes,
   dan alasan teknis di balik setiap keputusan non-obvious (banyak "kenapa
   bukan cara lain" yang penting dipahami sebelum ubah sesuatu).
5. [docs/CARA-MENJALANKAN.md](CARA-MENJALANKAN.md) — cara run + checklist
   sebelum go-live.
6. [docs/ROADMAP.md](ROADMAP.md) — apa yang **sengaja** belum/tidak
   dikerjakan, plus ide pengembangan lanjutan. **Dokumen ini (HANDOFF.md)
   meringkas ulang isi ROADMAP.md jadi lebih actionable — ROADMAP.md tetap
   sumber detailnya.**
7. [CLAUDE.md](../CLAUDE.md) (root) — instruksi kerja untuk AI assistant:
   invariant teknis yang tidak boleh dilanggar, gaya kerja bareng pemilik
   project. Namanya "CLAUDE.md" tapi isinya berlaku untuk AI apa pun yang
   mengerjakan project ini, bukan cuma Claude — baca ini juga walau
   toolingmu bukan Claude Code.

## 1. Verifikasi cepat: project ini masih hidup?

Sebelum mengerjakan apa pun, jalankan ini dulu untuk konfirmasi state
sekarang (jangan percaya dokumen ini/lainnya buta-buta soal "masih
jalan atau tidak"):

```bash
php artisan migrate:status   # semua migration harus "Ran"
php artisan test             # cuma 2 test default, tapi harus hijau
php artisan serve            # lalu buka http://127.0.0.1:8000
```

Per 2026-09-05: semua migration `Ran`, `php artisan test` hijau (2/2),
tidak ada perubahan kode sejak commit `c1844b7` (Phase 9). Database lokal
punya **satu order `ongoing` tersisa di Meja 12 (Rp76.000)** — ini
pesanan uji coba dari verifikasi Phase 9, dibiarkan sengaja supaya
pemilik bisa coba fitur pembayaran baru langsung. Kalau masih ada saat
kamu baca ini, artinya **fitur pembayaran Phase 9 belum pernah benar-benar
dipakai manusia lewat UI sungguhan** (baru diverifikasi lewat test
otomatis sementara + tinker) — lihat poin 3.5 di bawah.

## 2. Status Singkat

Phase 0–9 selesai dan terverifikasi (detail: [FITUR.md](FITUR.md)).
Ringkas: pemesanan berbasis meja lengkap (pilih meja → menu → keranjang →
kirim ke kasir → stok berkurang otomatis) + dashboard admin (kelola menu,
kategori, pantau meja aktif, **catat pembayaran & cetak struk** sejak
Phase 9). Satu putaran audit keamanan sudah dilakukan (lihat FITUR.md
bagian "Audit Keamanan & Perbaikan").

## 3. Yang Belum Selesai — Urut Prioritas

### 3.1. Celah keamanan lintas-meja (BELUM ditutup, butuh keputusan pemilik)

**Ini yang paling penting kalau website akan diakses dari internet
sungguhan** (bukan cuma wifi kafe). Siapa pun yang membuka
`/table/{1-36}/menu` dulu bisa menambah pesanan ke meja itu — nomor meja
bisa ditebak, dan tidak ada rahasia yang membuktikan "aku benar-benar
duduk di meja ini". Sudah dibatasi (rate limit + batas jumlah per item),
tapi belum ditutup total.

**Rancangan penutupnya sudah ada** di [ROADMAP.md](ROADMAP.md), bagian
"Token per meja di QR code", tapi **jangan dikerjakan tanpa tanya pemilik
dulu** — konsekuensinya
mengubah UX yang sekarang: dropdown "pilih meja manual" di halaman depan
harus dihapus atau diganti PIN. Ini keputusan produk, bukan keputusan
teknis.

### 3.2. Checklist go-live belum dijalankan

Selama masih dev di laptop, ini **jangan** diubah. Tapi kalau project ini
akan benar-benar dipasang di server produksi:
- `.env` produksi (bukan `.env.example`): `APP_DEBUG=false`,
  `APP_ENV=production`.
- Pastikan `ADMIN_PASSWORD` bukan default (per 2026-09-05 sudah aman di
  `.env` lokal — cek lagi kalau pindah environment).
- Baca poin 3.1 di atas kalau server akan diakses publik.

Detail: [docs/CARA-MENJALANKAN.md](CARA-MENJALANKAN.md), bagian
"Checklist Sebelum Go-Live".

### 3.3. Phase 10 — sudah "dijanjikan" di kode, belum dibangun

Ada komentar eksplisit `(Phase 10)` di `app/Models/Order.php` pada kolom
`last_item_added_at` (sudah diisi tiap kali keranjang di-submit, tapi
belum dibaca siapa pun). Idenya: dashboard admin auto-refresh (polling)
saat ada meja baru aktif, tanpa perlu klik "Muat Ulang" manual.
`config('kafeign.dashboard.poll_seconds')` (nilai 10) juga sudah
disiapkan sejak Phase 6, belum dipakai. **Fondasinya sudah ada, tinggal
dibangun** — tapi tetap tanya dulu ke pemilik sebelum mulai, ikuti pola
kerja di poin 5.

### 3.4. Ide pengembangan lain (belum diminta sama sekali, sekadar catatan)

Dari [ROADMAP.md](ROADMAP.md), bagian "Ide Pengembangan Lanjutan":
- **Laporan penjualan** — sejak Phase 9 lebih mudah dibangun (kolom
  `business_date`/`payment_method`/`total_frozen` sudah ada), tinggal
  halaman admin baru dengan `GROUP BY`. Belum dibangun.
- **Multi-admin dengan role berbeda** (kasir vs pemilik) — perlu kolom
  role baru di `users`.
- **Riwayat pesanan per meja untuk pelanggan** — beda dari halaman
  Riwayat admin (Phase 9); ini untuk pelanggan lihat kunjungan lama
  mereka sendiri, dan project ini tidak punya konsep akun pelanggan sama
  sekali (sesuai desain awal, bukan kelupaan).
- **Ukuran kertas struk untuk printer thermal** (58mm/80mm) — halaman
  `admin/receipt.blade.php` masih lebar browser-print biasa. Belum
  dikerjakan karena belum tahu printer apa yang akan dipakai kafe —
  **tanya pemilik dulu soal hardware-nya** sebelum bikin CSS `@page`.

### 3.5. Fitur Phase 9 belum diuji manusia lewat UI sungguhan

Pembayaran/struk (Phase 9) sudah diverifikasi lewat test otomatis
sementara (11 skenario, filenya sudah dihapus lagi setelah lulus — lihat
FITUR.md) dan alur keranjang lewat browser sungguhan, tapi **belum
pernah** ada yang benar-benar klik "Tandai Sudah Dibayar" di
`/admin/orders/{id}` lewat browser sungguhan sampai artikel ini ditulis
(order Meja 12 di atas masih `ongoing`). Rekomendasi: sebelum lanjut ke
fitur lain, pemilik login admin dan coba proses order itu (tunai & non-
tunai) sekali sungguhan, supaya ada verifikasi manusia yang nyata, bukan
cuma otomatis.

### 3.6. Sengaja TIDAK dikerjakan (jangan dianggap "kelupaan")

Dari permintaan awal pemilik, eksplisit di luar scope: **generate QR
code** (yang dibangun cuma website-nya), **payment gateway/pembayaran
online** (memang didesain tunai/QRIS/dll offline di kasir), **halaman
Booking/About/Contact** (ada di `legacy-static-site/` lama, tidak dibawa
ke Laravel), **multi-cabang/multi-cafe**. Jangan bangun ini tanpa
diminta eksplisit — detail alasannya di [ROADMAP.md](ROADMAP.md), bagian
"Yang Sengaja TIDAK Dikerjakan".

## 4. Keputusan yang HARUS ditanya ke pemilik dulu (jangan diputuskan sendiri oleh AI)

- Token QR per meja (3.1) — mengubah UX dropdown pilih meja.
- Kapan & di mana go-live sungguhan (3.2) — menentukan kapan checklist
  dijalankan.
- Nomor fase berikutnya kalau pemilik bilang "lanjut ke phase berikutnya"
  tanpa spesifik — **tanya dulu mau isinya apa**, jangan pilih sepihak
  dari daftar di 3.3/3.4. Ini sudah beberapa kali terjadi di riwayat
  project ini dan pemilik selalu diajak memilih dulu, bukan AI yang
  memutuskan.
- Hardware printer struk (3.4) — sebelum bikin CSS ukuran kertas.
- Perubahan ke keputusan desain di [docs/README.md](README.md) (tanpa
  emoji, tanpa foto produk default, tema warna, semua teks UI Bahasa
  Indonesia) — ini semua keputusan sadar pemilik, bukan default yang
  boleh diubah AI.

## 5. Kalau lanjut pakai AI lain — aturan main yang sudah berjalan di project ini

- **Satu fase = satu commit besar, langsung ke `master`**, tanpa
  branch/PR — per 2026-09-05 ada 20 commit di riwayat (`git log`), semuanya
  linear di `master`, tidak ada branch lain, tidak ada merge commit. Ikuti
  pola ini kecuali pemilik minta workflow lain.
- **Tidak ada automated test suite permanen** — cuma 2 test default
  Laravel. Setiap fase diverifikasi manual (browser + `tinker` + kadang
  test PHPUnit sementara yang dihapus lagi) dan dicatat naratif di
  FITUR.md. Ini pilihan sadar, bukan kemalasan — kalau AI berikutnya mau
  mengubah ke automated test permanen, itu juga keputusan yang sebaiknya
  dikonfirmasi dulu ke pemilik, bukan diam-diam ditambahkan.
- **Pemilik masih belajar Laravel/PHP** — jelaskan keputusan teknis
  dengan bahasa jelas, jangan asumsikan familiar dengan istilah framework.
- Komunikasi dalam Bahasa Indonesia santai.
- Baca invariant teknis di [CLAUDE.md](../CLAUDE.md) bagian "Fakta Cepat"
  sebelum menyentuh apa pun yang berhubungan dengan uang (`orders`,
  `order_items`, stok, pembayaran) — ada beberapa jebakan yang sudah
  pernah kejadian dan diperbaiki (race condition, SQLite yang tidak
  mendukung row-lock, dll), jangan sampai terulang karena tidak baca dulu.
