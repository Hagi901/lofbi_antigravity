<?php

use App\Http\Controllers\Api\LaporanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Laporan
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Laporan & Ekspor: admin, operator, validator, pimpinan
|
*/

Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/laporan/baop', [LaporanController::class, 'baop']);
    Route::get('/laporan/dbr', [LaporanController::class, 'dbr']);
    Route::get('/laporan/nilai-buku', [LaporanController::class, 'nilaiBuku']);
    Route::get('/laporan/export', [LaporanController::class, 'export']);
});
