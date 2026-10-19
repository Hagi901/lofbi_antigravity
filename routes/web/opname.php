<?php

use App\Http\Controllers\Web\OpnameController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Opname Fisik
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Buat Sesi & Input Fisik: admin, operator
| - Approval Opname: validator
| - Lihat Sesi & Detail: admin, operator, validator (pimpinan tidak memiliki akses)
|
*/

// Membuat Sesi & Input Hasil Fisik (Admin, Operator)
Route::middleware('role:admin,operator')->group(function () {
    Route::get('/opname/create', [OpnameController::class, 'create'])
        ->name('opname.create');

    Route::post('/opname', [OpnameController::class, 'store'])
        ->name('opname.store');

    Route::get('/opname/{id}/input-fisik', [OpnameController::class, 'inputFisik'])
        ->name('opname.input_fisik');

    Route::post('/opname/{id}/save-fisik', [OpnameController::class, 'saveFisik'])
        ->name('opname.save_fisik');
});

// Approval Opname (Validator)
Route::middleware('role:validator')->group(function () {
    Route::post('/opname/{id}/approve', [OpnameController::class, 'approve'])
        ->name('opname.approve');

    Route::post('/opname/{id}/reject', [OpnameController::class, 'reject'])
        ->name('opname.reject');
});

// Lihat Opname (Admin, Operator, Validator - Pimpinan DILARANG)
Route::middleware('role:admin,operator,validator')->group(function () {
    Route::get('/opname', [OpnameController::class, 'index'])
        ->name('opname.index');

    Route::get('/opname/{id}', [OpnameController::class, 'show'])
        ->name('opname.show');
});
