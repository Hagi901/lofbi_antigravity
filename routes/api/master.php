<?php

use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Master Data (Ruangan & Kategori)
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Master Data: admin, operator, validator, pimpinan (lihat)
|
*/

Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/ruangan', [MasterDataController::class, 'ruangan']);
    Route::get('/kategori', [MasterDataController::class, 'kategori']);
});
