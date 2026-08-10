# Kafeign

Aplikasi pemesanan berbasis meja untuk kafe fisik **Kafeign** — Laravel
13 + SQLite. Pelanggan duduk di salah satu dari 36 meja, pilih nomor
mejanya di website, pesan dari menu asli kafe, dan pesanannya jadi tab
berjalan untuk meja itu sampai kasir membersihkannya lewat dashboard admin.

## Dokumentasi

Semua konteks project — apa yang sudah dibangun, skema database, cara
menjalankan — ada di [`docs/`](docs/README.md):

- [docs/README.md](docs/README.md) — ringkasan bisnis & tech stack
- [docs/FITUR.md](docs/FITUR.md) — daftar fitur yang sudah jalan
- [docs/ARSITEKTUR.md](docs/ARSITEKTUR.md) — skema database, routes, model
- [docs/CARA-MENJALANKAN.md](docs/CARA-MENJALANKAN.md) — cara run di lokal
- [docs/ROADMAP.md](docs/ROADMAP.md) — apa yang sengaja di luar scope

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan storage:link
# isi ADMIN_EMAIL & ADMIN_PASSWORD di .env dulu sebelum baris berikut
php artisan migrate --seed
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`. Detail lengkap ada di
[docs/CARA-MENJALANKAN.md](docs/CARA-MENJALANKAN.md).

## Tech Stack

PHP 8.4 · Laravel 13 · SQLite · Blade · Tailwind CSS v4 · Vite ·
vanilla JavaScript (tanpa framework frontend)

---

<sup>Dibangun dengan bantuan <a href="https://claude.com/claude-code">Claude Code</a>.</sup>
