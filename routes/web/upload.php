<?php

use App\Http\Controllers\Web\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Modul Upload & Validasi Staging
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Lihat Paket & Baris: admin, operator, validator (pimpinan tidak memiliki akses)
| - Unggah Berkas & Revisi: admin, operator
| - Validasi Baris & Setujui Semua: HANYA validator
|
*/

// Melihat paket & baris (Admin, Operator, Validator - Pimpinan DILARANG)
Route::middleware('role:admin,operator,validator')->group(function () {
    Route::get('/upload', [UploadController::class, 'index'])
        ->name('upload.index');

    Route::get('/upload/{id}', [UploadController::class, 'show'])
        ->name('upload.show');
});

// Mengunggah file & revisi (Admin & Operator)
Route::middleware('role:admin,operator')->group(function () {
    Route::post('/upload', [UploadController::class, 'store'])
        ->name('upload.store');

    Route::post('/upload/{id}/revisi', [UploadController::class, 'revisi'])
        ->name('upload.revisi');
});

// Validasi per baris & setujui semua (HANYA Validator)
Route::middleware('role:validator')->group(function () {
    Route::post('/upload/baris/{barisId}/setujui', [UploadController::class, 'setujuiBaris'])
        ->name('upload.baris.setujui');

    Route::post('/upload/baris/{barisId}/tolak', [UploadController::class, 'tolakBaris'])
        ->name('upload.baris.tolak');

    Route::post('/upload/{id}/setujui-semua', [UploadController::class, 'setujuiSemua'])
        ->name('upload.setujui_semua');
});
