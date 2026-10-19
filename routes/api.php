<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - LOFBI REST API
|--------------------------------------------------------------------------
|
| Dokumentasi spesifikasi lengkap tersedia di docs/api.md.
| Seluruh endpoint di bawah grup auth:sanctum dilindungi otentikasi Bearer token.
|
*/

// Autentikasi Publik & Sesi User
require __DIR__.'/api/auth.php';

// Rute Terlindungi - Wajib Token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    require __DIR__.'/api/dashboard.php';
    require __DIR__.'/api/aset.php';
    require __DIR__.'/api/persediaan.php';
    require __DIR__.'/api/opname.php';
    require __DIR__.'/api/laporan.php';
    require __DIR__.'/api/monitoring.php';
    require __DIR__.'/api/pengaturan.php';
    require __DIR__.'/api/master.php';
});
