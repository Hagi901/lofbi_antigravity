<?php

use App\Http\Controllers\Api\OpnameController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Opname Fisik
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Membuat Opname: admin, operator
| - Melihat Riwayat & Referensi Ruangan: admin, operator, validator (pimpinan tidak memiliki akses)
|
*/

// Membuat Opname -> Admin & Operator
Route::middleware('role:admin,operator')->group(function () {
    Route::post('/opname', [OpnameController::class, 'store']);
});

// Melihat Opname -> Admin, Operator, Validator (Pimpinan DILARANG)
Route::middleware('role:admin,operator,validator')->group(function () {
    Route::get('/opname/riwayat', [OpnameController::class, 'riwayat']);
    Route::get('/opname/ruangan/{ruangan}', [OpnameController::class, 'ruangan']);
});
