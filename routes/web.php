<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OpnameController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes — LOFBI Fullstack Web Interface
|--------------------------------------------------------------------------
| Antarmuka Blade Frontend LOFBI (KSOP Kelas I Banten)
|
*/

// ── Rute Autentikasi Publik ───────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout.post');

// ── Rute Terlindungi (Wajib Login Web Session) ───────────────────────────
Route::middleware(['auth'])->group(function () {
    
    // Redirect root ke /dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // 1. Dashboard Overview
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Modul Manajemen Aset
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');
    Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');

    // 3. Modul Persediaan & FIFO
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/in', [InventoryController::class, 'createIn'])->name('inventory.in.create');
    Route::post('/inventory/in', [InventoryController::class, 'storeIn'])->name('inventory.in.store');
    Route::get('/inventory/out', [InventoryController::class, 'createOut'])->name('inventory.out.create');
    Route::post('/inventory/out', [InventoryController::class, 'storeOut'])->name('inventory.out.store');

    // 4. Modul Opname Fisik
    Route::get('/opname', [OpnameController::class, 'index'])->name('opname.index');
    Route::get('/opname/create', [OpnameController::class, 'create'])->name('opname.create');
    Route::post('/opname', [OpnameController::class, 'store'])->name('opname.store');

    // 5. Modul Laporan & Export
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/aset/pdf', [ReportController::class, 'exportAsetPdf'])->name('reports.aset.pdf');

    // 6. Profil, Pengaturan & Notifikasi
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});