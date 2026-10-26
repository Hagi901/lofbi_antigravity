<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Autentikasi, Profil & Notifikasi
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Login & Logout: Publik / Sesi Aktif
| - Profil & Notifikasi: Semua role yang terautentikasi (admin, operator, validator, pimpinan)
|
*/

// Rute Autentikasi Publik
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('login.post');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

// Rute Profil & Notifikasi (Semua Role Terautentikasi)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile.index');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.markAllRead');

    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])
        ->name('notifications.markRead');
});
