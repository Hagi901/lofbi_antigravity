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
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes — LOFBI Fullstack Web Interface
|--------------------------------------------------------------------------
| Role Matrix:
|   admin     → Semua akses penuh
|   operator  → Input barang masuk/keluar, lihat aset & persediaan
|   validator → Approve/reject pengajuan, lihat semua (read)
|   pimpinan  → Read-only + laporan
|   viewer    → Read-only semua halaman
*/

// ── Rute Autentikasi Publik ───────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout.post');

// ── Rute Terlindungi (Wajib Login) ───────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Redirect root ke /dashboard
    Route::get('/', fn () => redirect()->route('dashboard'));

    // ── 1. Dashboard (semua role boleh) ───────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── 2. Manajemen Aset ─────────────────────────────────────────────────
    // Tambah / Edit / Hapus → hanya admin & operator (create ditaruh sebelum {id})
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');
    });
    // Lihat daftar & detail → semua role boleh
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');

    // ── 3. Persediaan & FIFO ──────────────────────────────────────────────
    // Tambah master, input masuk/keluar, edit, hapus → admin & operator
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/in', [InventoryController::class, 'createIn'])->name('inventory.in.create');
        Route::post('/inventory/in', [InventoryController::class, 'storeIn'])->name('inventory.in.store');
        Route::get('/inventory/out', [InventoryController::class, 'createOut'])->name('inventory.out.create');
        Route::post('/inventory/out', [InventoryController::class, 'storeOut'])->name('inventory.out.store');
        Route::get('/inventory/{id}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // Validasi pengajuan → hanya admin & validator
    Route::middleware('role:admin,validator')->group(function () {
        Route::get('/inventory/pengajuan', [InventoryController::class, 'pengajuan'])->name('inventory.pengajuan');
        Route::patch('/inventory/{id}/approve', [InventoryController::class, 'approve'])->name('inventory.approve');
        Route::patch('/inventory/{id}/reject', [InventoryController::class, 'reject'])->name('inventory.reject');
    });

    // Lihat kartu stok & detail buku persediaan → semua role boleh
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show');

    // ── 4. Opname Fisik ───────────────────────────────────────────────────
    // Buat sesi opname → hanya admin & operator (create sebelum {id})
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/opname/create', [OpnameController::class, 'create'])->name('opname.create');
        Route::post('/opname', [OpnameController::class, 'store'])->name('opname.store');
        Route::get('/opname/{id}/input-fisik', [OpnameController::class, 'inputFisik'])->name('opname.input_fisik');
        Route::post('/opname/{id}/save-fisik', [OpnameController::class, 'saveFisik'])->name('opname.save_fisik');
    });
    // Approve & reject → khusus validator
    Route::middleware('role:admin,validator')->group(function () {
        Route::post('/opname/{id}/approve', [OpnameController::class, 'approve'])->name('opname.approve');
        Route::post('/opname/{id}/reject', [OpnameController::class, 'reject'])->name('opname.reject');
    });
    // Lihat daftar & detail → semua role boleh
    Route::get('/opname', [OpnameController::class, 'index'])->name('opname.index');
    Route::get('/opname/{id}', [OpnameController::class, 'show'])->name('opname.show');

    // ── 5. Laporan & Export (semua role boleh lihat & download) ──────────
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/aset/pdf', [ReportController::class, 'exportAsetPdf'])->name('reports.aset.pdf');
    Route::get('/reports/aset/excel', [ReportController::class, 'exportAsetExcel'])->name('reports.aset.excel');
    Route::get('/reports/persediaan/pdf', [ReportController::class, 'exportPersediaanPdf'])->name('reports.persediaan.pdf');
    Route::get('/reports/persediaan/excel', [ReportController::class, 'exportPersediaanExcel'])->name('reports.persediaan.excel');
    Route::get('/reports/opname/pdf', [ReportController::class, 'exportOpnamePdf'])->name('reports.opname.pdf');

    // ── 6. Profil & Notifikasi (semua role) ──────────────────────────────
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // ── 7. Pengaturan Sistem → hanya admin ───────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    });
});