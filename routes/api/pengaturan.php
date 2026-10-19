<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Pengaturan, Backup, & Audit Trail
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Pengaturan, Backup, & Audit Trail: HANYA admin
|
*/

// Audit Trail (Admin)
Route::middleware('role:admin')->group(function () {
    Route::get('/audit-trail', [AuditController::class, 'index']);
});

// Settings & Backup (Admin)
Route::middleware('role:admin')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'store']);
    Route::post('/backup', [SettingsController::class, 'backup']);
});
