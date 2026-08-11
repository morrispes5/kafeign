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

];
