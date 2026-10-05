<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Memungkinkan Frontend (dari port mana pun, misal 8080, 3000, 5173)
    | untuk mengakses REST API backend tanpa hambatan CORS.
    |
    | ⚠️ PRODUCTION: defaultnya '*' (semua domain diizinkan) supaya development
    | lokal tetap mudah. Sebelum deploy, atur CORS_ALLOWED_ORIGINS di .env
    | dengan daftar domain yang dipisah koma, contoh:
    |   CORS_ALLOWED_ORIGINS=https://lofbi.ksopbanten.go.id,https://app.lofbi.id
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', '*'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
