<?php

use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Dashboard
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Dashboard: admin, operator, validator, pimpinan (lihat)
|
*/

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});
