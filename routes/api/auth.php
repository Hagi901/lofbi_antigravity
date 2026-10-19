<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Autentikasi
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Login: Publik (throttle:6,1)
| - Logout & Profil Me: Semua user terautentikasi (auth:sanctum)
|
*/

// Login publik
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1');

// Sesi terautentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});
