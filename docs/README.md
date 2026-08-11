# Dokumentasi Kafeign

> Kalau kamu Claude (atau AI lain) baru masuk ke project ini di sesi/chat baru:
> baca file ini dulu, lalu [FITUR.md](FITUR.md) untuk tahu apa yang sudah jalan
> sebelum mengerjakan apa pun. Jangan asumsikan fitur belum ada tanpa cek dulu.

## Ini web apa?

**Kafeign** adalah aplikasi web pemesanan berbasis meja untuk kafe fisik
bernama Kafeign. Alurnya:

1. Pelanggan datang ke kafe, duduk di salah satu dari **36 meja**.
2. Pelanggan membuka website (nantinya lewat scan QR code di meja, tapi
   QR generation-nya sendiri di luar scope — yang dibangun cuma
   website-nya), lalu pilih/masukkan nomor mejanya.
3. Pelanggan melihat menu (data asli dari kafe, bukan contoh), pesan
   kopi/cemilan — pesanan langsung tersimpan sebagai **tab/tagihan
   berjalan** untuk meja itu.
4. Kalau pelanggan pesan lagi nanti (jam berbeda, meja sama), sistem
   otomatis menyambung ke tab yang sama, bukan bikin pesanan baru.
5. Pembayaran dilakukan **offline** di kasir (tidak ada payment gateway).
   Staf kasir login ke **dashboard admin**, lihat meja mana yang masih
   aktif + totalnya, lalu "bersihkan" meja itu setelah pelanggan bayar —
   meja itu baru bisa dipakai pelanggan berikutnya dari nol.
6. Admin juga bisa kelola isi menu (tambah/ubah/nonaktifkan item &
   kategori, upload foto) langsung dari dashboard, tanpa sentuh kode.

Ini **refactor total** dari project awal pemilik (HTML/CSS/JS statis,
tanpa backend sama sekali — lihat `legacy-static-site/` di root project,
disimpan cuma sebagai arsip referensi visual, tidak dipakai lagi).

## Tech Stack

| Bagian | Teknologi |
|---|---|
| Backend | PHP 8.4 + Laravel 13 |
| Database | SQLite (`database/database.sqlite`) |
| Frontend | Blade (server-rendered) + Tailwind CSS v4 + vanilla JS (fetch, tanpa framework JS) |
| Build tool | Vite (`laravel-vite-plugin`) |
| Font | Fraunces (heading/wordmark) + Instrument Sans (body), self-hosted via Bunny Fonts |
| Auth admin | Laravel `Auth` bawaan (session), 1 akun cashier di tabel `users` |

**Keputusan desain yang perlu diingat** (jangan diubah tanpa tanya user dulu):
- **Tanpa emoji di mana pun** — fokus tipografi, terasa profesional.
- **Tanpa foto produk bawaan** — hanya ikon SVG custom per kategori (lihat
  `resources/views/components/icon.blade.php`). Admin *bisa* upload foto
  per item lewat dashboard (Phase 4), tapi tidak ada foto default.
- **Tema warna**: coklat maroon/terracotta + krem + aksen amber, diambil
  dari foto asli kafe (bukan warna dari `legacy-static-site/` yang lama).
- Semua teks UI Bahasa Indonesia.

## Peta Dokumen Ini

| File | Isinya |
|---|---|
| [FITUR.md](FITUR.md) | Daftar lengkap fitur yang **sudah jalan**, per fase pengerjaan |
| [ARSITEKTUR.md](ARSITEKTUR.md) | Skema database, model, routes, struktur folder |
| [CARA-MENJALANKAN.md](CARA-MENJALANKAN.md) | Cara run project ini di lokal |
| [ROADMAP.md](ROADMAP.md) | Apa yang belum/tidak dikerjakan, ide pengembangan lanjutan |

Lihat juga [PANDUAN-STRUKTUR.md](../PANDUAN-STRUKTUR.md) di root project untuk
peta folder Laravel (mana yang kode asli, mana yang otomatis/jangan disentuh).

## Riwayat Kerja (ringkas)

Project ini dikerjakan bertahap, tiap fase adalah satu commit git besar
di riwayat (`git log`). Urutannya:

1. **Phase 0** — Setup Laravel + SQLite, skema database, seed data menu asli
2. **Phase 1** — Tampilan depan: landing page pilih meja + halaman menu
3. **Phase 2** — Logic pemesanan sungguhan: tambah ke tab, tab berjalan per meja
4. **Phase 3** — Dashboard admin: login, pantau meja aktif, bersihkan meja
5. **Phase 4** — Admin bisa kelola menu (CRUD) + upload foto
6. **Phase 5** — Polish: halaman error custom, hapus item dari pesanan, dll
7. **Phase 6** — Fondasi: server bisa kirim pesan JSON ke pelanggan, SQLite
   tahan konkurensi, sistem toast
8. **Phase 7** — Keranjang: tap "+" masuk keranjang privat dulu, baru jadi
   order sungguhan setelah pelanggan menekan "Kirim Pesanan"
9. **Phase 8** — Stok otomatis per item, berkurang saat submit, item habis
   tetap tampil (bukan hilang)
10. **Phase 9** — Pembayaran & struk: metode bayar, kembalian otomatis
    untuk tunai, nomor struk berurutan, total dibekukan saat lunas

Di antaranya juga ada satu putaran audit keamanan dan perbaikan foto
menu (bukan "phase" bernomor, tapi ikut tercatat di
[FITUR.md](FITUR.md)). Detail lengkap tiap fase ada di situ.
