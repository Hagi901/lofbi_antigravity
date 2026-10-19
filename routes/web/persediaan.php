<?php

use App\Http\Controllers\Web\PersediaanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Manajemen Persediaan & Pengajuan Barang Keluar
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Edit & Hapus Master Persediaan: admin, operator
| - Pengajuan Barang Keluar: buat oleh admin, operator; lihat oleh admin, validator; approval oleh validator
| - Lihat Persediaan: admin, operator, validator, pimpinan
|
*/

// Edit & Hapus Master Persediaan (Admin, Operator)
Route::middleware('role:admin,operator')->group(function () {
    Route::get('/inventory/{id}/edit', [PersediaanController::class, 'edit'])
        ->name('inventory.edit');

    Route::put('/inventory/{id}', [PersediaanController::class, 'update'])
        ->name('inventory.update');

    Route::delete('/inventory/{id}', [PersediaanController::class, 'destroy'])
        ->name('inventory.destroy');
});

// Pengajuan Barang Keluar - Buat (Admin, Operator)
Route::middleware('role:admin,operator')->group(function () {
    Route::get('/inventory/out', [PersediaanController::class, 'createOut'])
        ->name('inventory.out.create');

    Route::post('/inventory/out', [PersediaanController::class, 'storeOut'])
        ->name('inventory.out.store');
});

// Daftar Pengajuan Barang Keluar (Admin, Validator)
Route::middleware('role:admin,validator')->group(function () {
    Route::get('/inventory/pengajuan', [PersediaanController::class, 'pengajuan'])
        ->name('inventory.pengajuan');
});

// Approval Pengajuan Barang Keluar (Validator)
Route::middleware('role:validator')->group(function () {
    Route::patch('/inventory/{id}/approve', [PersediaanController::class, 'approve'])
        ->name('inventory.approve');

    Route::patch('/inventory/{id}/reject', [PersediaanController::class, 'reject'])
        ->name('inventory.reject');
});

// Lihat Persediaan (Admin, Operator, Validator, Pimpinan)
Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/inventory', [PersediaanController::class, 'index'])
        ->name('inventory.index');

    Route::get('/inventory/{id}', [PersediaanController::class, 'show'])
        ->name('inventory.show');
});
