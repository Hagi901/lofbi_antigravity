<?php

use App\Http\Controllers\Web\AsetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Manajemen Aset
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Edit & Hapus Aset: admin, operator
| - Lihat Daftar & Detail Aset: admin, operator, validator, pimpinan
|
*/

// Edit & Hapus Aset (Admin, Operator)
Route::middleware('role:admin,operator')->group(function () {
    Route::get('/assets/{id}/edit', [AsetController::class, 'edit'])
        ->name('assets.edit');

    Route::put('/assets/{id}', [AsetController::class, 'update'])
        ->name('assets.update');

    Route::delete('/assets/{id}', [AsetController::class, 'destroy'])
        ->name('assets.destroy');
});

// Lihat Aset (Admin, Operator, Validator, Pimpinan)
Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/assets', [AsetController::class, 'index'])
        ->name('assets.index');

    Route::get('/assets/{id}', [AsetController::class, 'show'])
        ->name('assets.show');
});
