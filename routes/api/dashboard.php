<?php

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Dashboard
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Ringkasan Dashboard: admin, operator, validator, pimpinan (lihat)
|
*/

Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
});
