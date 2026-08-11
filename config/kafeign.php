<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Akun Admin / Kasir
    |--------------------------------------------------------------------------
    |
    | Kredensial yang dipakai AdminUserSeeder untuk membuat (atau
    | memperbarui) satu akun admin setiap kali database di-seed.
    |
    | Dibaca lewat config, bukan env() langsung di dalam seeder/command:
    | begitu `php artisan config:cache` dijalankan — yang normal di
    | produksi — semua panggilan env() di luar file config akan
    | mengembalikan null, dan seeder-nya akan gagal tanpa sebab yang
    | jelas. Lewat config seperti ini, nilainya ikut ter-cache.
    |
    */

    'admin' => [
        'name' => env('ADMIN_NAME', 'Kafeign Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Foto Menu
    |--------------------------------------------------------------------------
    |
    | Batas ukuran unggahan, dalam kilobyte. Dipakai DUA tempat: aturan
    | validasi di form request, dan label yang dibaca admin di form.
    | Sebelumnya dua angka itu ditulis terpisah dan sempat melenceng —
    | form bilang 2MB sementara server menerima 10MB. Satu sumber di sini
    | supaya tidak bisa terulang.
    |
    | Catatan: nilai ini juga harus muat di `upload_max_filesize` dan
    | `post_max_size` pada php.ini, kalau tidak PHP menolak duluan sebelum
    | Laravel sempat memvalidasi.
    |
    */

    'menu_photo' => [
        'max_kb' => 10240,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hari Bisnis
    |--------------------------------------------------------------------------
    |
    | Jam mulai hari bisnis. Hari D berjalan dari [D 04:00, D+1 04:00),
    | jadi pelunasan jam 00:10 masuk ke hari sebelumnya — sesuai cara
    | pemilik kafe menghitung "penjualan tadi malam".
    |
    | Nilai ini hanya dipakai saat MENENTUKAN business_date pada pelunasan.
    | Setelah tersimpan di kolom, laporan lama tidak akan berubah meski
    | angka ini diubah nanti.
    |
    */

    'business_day' => [
        'start_hour' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pesanan
    |--------------------------------------------------------------------------
    |
    | Berapa menit pelanggan masih boleh menghapus sendiri item yang sudah
    | dikirim ke kasir. Lewat itu, barista kemungkinan sudah membuatnya —
    | mengembalikan stok jadi salah dan kafe kelebihan jual besoknya.
    | Set 0 untuk mematikan batas ini (perilaku lama: bebas kapan saja).
    |
    */

    'order' => [
        'self_delete_window_minutes' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Kasir
    |--------------------------------------------------------------------------
    |
    | Jeda polling dashboard, dalam detik.
    |
    */

    'dashboard' => [
        'poll_seconds' => 10,
    ],

];
