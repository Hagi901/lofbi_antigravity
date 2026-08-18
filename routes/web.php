<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — LOFBI API (Pure Backend)
|--------------------------------------------------------------------------
| Aplikasi ini berjalan sebagai pure REST API.
| Seluruh endpoint tersedia di routes/api.php (prefix /api).
|
| Route ini hanya melayani health-check sederhana di root URL.
*/

Route::get('/', function () {
    return response()->json([
        'app'     => 'LOFBI — Laporan Opname Fisik Barang & Inventarisasi',
        'version' => '1.0.0',
        'unit'    => 'KSOP Kelas I Banten — Kementerian Perhubungan RI',
        'api'     => url('/api'),
        'status'  => 'running',
    ]);
});