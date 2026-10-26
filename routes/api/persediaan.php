<?php

use App\Http\Controllers\Api\PersediaanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Manajemen Persediaan & Pengajuan Keluar
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Lihat Persediaan: admin, operator, validator, pimpinan
| - Edit Master Persediaan & Buat Pengajuan: admin, operator
| - Lihat Daftar Pengajuan: admin, validator
| - Validasi Pengajuan (Setujui/Tolak): validator saja
|
*/

// Daftar Pengajuan Barang Keluar - Lihat (Admin & Validator)
Route::middleware('role:admin,validator')->group(function () {
    Route::get('/persediaan/pengajuan', [PersediaanController::class, 'pengajuan']);
});

// Semua role authenticated dapat melihat Persediaan
Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/persediaan/ringkas', [PersediaanController::class, 'ringkas']);
    Route::get('/persediaan/jenis/{jenisBarang}/detail', [PersediaanController::class, 'detailByJenis']);
    Route::get('/persediaan/{persediaan}/batch', [PersediaanController::class, 'batch']);
    Route::get('/persediaan/{persediaan}', [PersediaanController::class, 'show']);
});

// Edit master persediaan -> Admin & Operator
Route::middleware('role:admin,operator')->group(function () {
    Route::put('/persediaan/{persediaan}', [PersediaanController::class, 'update']);
    Route::patch('/persediaan/{persediaan}', [PersediaanController::class, 'update']);
    Route::post('/persediaan/{persediaan}/pengajuan-keluar', [PersediaanController::class, 'pengajuanKeluar']);
});

// Validasi Pengajuan Barang Keluar -> Validator saja
Route::middleware('role:validator')->group(function () {
    Route::post('/persediaan/pengajuan/{transaksi}/setujui', [PersediaanController::class, 'setujui']);
    Route::post('/persediaan/pengajuan/{transaksi}/tolak', [PersediaanController::class, 'tolak']);
});
