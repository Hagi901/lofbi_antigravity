<?php

use App\Http\Controllers\Api\AsetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Manajemen Aset
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Lihat Data Aset: admin, operator, validator, pimpinan
| - Edit & Hapus Aset: admin, operator
|
*/

// Semua role authenticated dapat melihat data Aset
Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/aset/ringkas', [AsetController::class, 'ringkas']);
    Route::get('/aset/jenis/{jenisBarang}/unit', [AsetController::class, 'unit']);
    Route::get('/aset/{aset}', [AsetController::class, 'show']);
    Route::get('/aset/{aset}/riwayat', [AsetController::class, 'riwayat']);
    Route::get('/aset/{aset}/qr', [AsetController::class, 'qr']);
});

// Edit & Hapus Aset -> Admin & Operator
Route::middleware('role:admin,operator')->group(function () {
    Route::put('/aset/{aset}', [AsetController::class, 'update']);
    Route::patch('/aset/{aset}', [AsetController::class, 'update']);
    Route::delete('/aset/{aset}', [AsetController::class, 'destroy']);
});
