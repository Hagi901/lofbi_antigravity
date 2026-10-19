<?php

use App\Http\Controllers\Web\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Pengaturan Sistem & Backup
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Pengaturan & Backup: HANYA admin
|
*/

Route::middleware('role:admin')->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings', [SettingController::class, 'update'])
        ->name('settings.update');

    Route::get('/settings/backup', [SettingController::class, 'backup'])
        ->name('settings.backup');
});
