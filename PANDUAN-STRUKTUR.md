# Panduan Struktur Folder — Kafeign

Setelah refactor ke Laravel, banyak folder di sidebar VS Code itu memang
bawaan framework — bukan sesuatu yang perlu kamu buka/edit. Tabel di bawah
memetakan semuanya jadi 4 kategori. `.vscode/settings.json` sudah
disetel supaya kategori **[OTOMATIS]** langsung disembunyikan dari Explorer,
jadi kalau kamu buka VS Code sekarang harusnya sidebar sudah jauh lebih
sepi dari screenshot sebelumnya.

## [KODE UTAMA] — di sinilah kita akan kerja tiap phase

| Folder | Isinya |
|---|---|
| `app/Models/` | Category, MenuItem, Table, Order, OrderItem — representasi data di kode |
| `app/Http/Controllers/` | Logic tiap halaman/aksi (nanti diisi mulai Phase 1–2) |
| `app/Enums/` | `OrderStatus.php` (ongoing/paid/cancelled) |
| `database/migrations/` | Struktur tabel database |
| `database/seeders/` | Data awal (menu asli, 36 meja, admin) |
| `resources/views/` | Tampilan halaman (Blade) — belum ada isinya, mulai Phase 1 |
| `resources/css/`, `resources/js/` | Styling & JS ringan (dark mode toggle, dst) |
| `routes/web.php` | Daftar semua URL/halaman website |

## [KONFIGURASI] — jarang diutak-atik, cukup tahu isinya

| Folder/File | Fungsinya |
|---|---|
| `.env` | Pengaturan lokal: koneksi database, kredensial admin. **Jangan pernah dikirim/di-share ke siapa pun.** |
| `config/app.php` | Nama app, timezone, locale |
| `composer.json` / `package.json` | Daftar dependency PHP/JS |
| `routes/console.php` | Perintah artisan custom (belum dipakai) |

## [OTOMATIS] — dibuat ulang sendiri oleh Composer/npm/Laravel, JANGAN diedit manual

Sudah disembunyikan dari Explorer lewat `.vscode/settings.json`:

| Folder/File | Kenapa disembunyikan |
|---|---|
| `vendor/` | Semua package PHP dari Composer — ribuan file, bukan kode kita |
| `node_modules/` | Package JS dari npm (muncul mulai Phase 1 setelah `npm install`) |
| `bootstrap/cache/`, `storage/framework/` | Cache internal Laravel, auto-generate |
| `public/build/`, `public/hot` | Hasil compile CSS/JS dari Vite |
| `database/database.sqlite` | File database asli (biner, bukan teks) — kalau mau **lihat isinya**, install extension **SQLite Viewer** (sudah direkomendasikan) lalu klik file ini, tampil sebagai tabel |

Masih ada di disk, cuma disembunyikan dari sidebar. Tidak hilang, tidak
terhapus.

## [ARSIP] — referensi lama, tidak aktif lagi

| Folder | Keterangan |
|---|---|
| `legacy-static-site/` | Website HTML/CSS/JS lama kamu sebelum di-refactor. Disimpan cuma sebagai referensi warna/copy asli, tidak lagi dipakai/di-load oleh apa pun. Boleh dihapus kapan pun kalau sudah tidak dibutuhkan. |

---

**Soal `.vscode/settings.json` yang lama**: isinya cuma satu baris API key
extension "AskCodi" yang sempat ke-expose di file itu (tidak terkait Claude
Code). Sudah saya bersihkan dan ganti dengan pengaturan Explorer di atas —
kalau kamu memang masih pakai AskCodi extension, tinggal bilang, nanti saya
tambahkan lagi (tapi API key aslinya harus kamu rotate dulu di dashboard
AskCodi, jangan pakai yang lama karena sempat ke-expose).
