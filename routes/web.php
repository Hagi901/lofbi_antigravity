<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\PersediaanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OpnameController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| Web Routes — LOFBI Fullstack Web Interface
|--------------------------------------------------------------------------
|
| Role Matrix:
|
|   admin
|   - Kelola Aset
|   - Kelola Persediaan
|   - Kelola Master Barang
|   - Membuat pengajuan barang keluar
|   - Membuat dan input Opname
|   - Import data
|   - Pengaturan & backup
|   - Laporan
|
|   validator
|   - Melihat data Aset & Persediaan
|   - Approve / Reject pengajuan barang keluar
|   - Approve / Reject hasil Opname
|
|   pimpinan
|   - Dashboard
|   - Melihat Aset
|   - Melihat Persediaan
|   - Melihat Opname
|   - Laporan
|   - Monitoring
|
*/

// ─────────────────────────────────────────────────────────────────────────────
// RUTE AUTENTIKASI PUBLIK
// ─────────────────────────────────────────────────────────────────────────────

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('login.post');

Route::get('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout.post');


// ─────────────────────────────────────────────────────────────────────────────
// RUTE TERLINDUNGI — WAJIB LOGIN
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth'])->group(function () {

    // ─────────────────────────────────────────────────────────────────────────
    // ROOT
    // ─────────────────────────────────────────────────────────────────────────

    Route::get('/', fn () => redirect()->route('dashboard'));


    // ─────────────────────────────────────────────────────────────────────────
    // 1. DASHBOARD
    // Admin & Pimpinan
    // ─────────────────────────────────────────────────────────────────────────

    Route::middleware('role:admin,pimpinan')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

    });


    // ─────────────────────────────────────────────────────────────────────────
    // 2. MANAJEMEN ASET
    // ─────────────────────────────────────────────────────────────────────────

    // Edit & Hapus Aset → Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/assets/{id}/edit', [AsetController::class, 'edit'])
            ->name('assets.edit');

        Route::put('/assets/{id}', [AsetController::class, 'update'])
            ->name('assets.update');

        Route::delete('/assets/{id}', [AsetController::class, 'destroy'])
            ->name('assets.destroy');
    });

    // Lihat Aset → Admin, Validator, Pimpinan
    Route::middleware('role:admin,validator,pimpinan')->group(function () {

        Route::get('/assets', [AsetController::class, 'index'])
            ->name('assets.index');

        Route::get('/assets/{id}', [AsetController::class, 'show'])
            ->name('assets.show');
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 3. PERSEDIAAN
    // ─────────────────────────────────────────────────────────────────────────

    // Edit & Hapus Master Persediaan → Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/inventory/{id}/edit', [PersediaanController::class, 'edit'])
            ->name('inventory.edit');

        Route::put('/inventory/{id}', [PersediaanController::class, 'update'])
            ->name('inventory.update');

        Route::delete('/inventory/{id}', [PersediaanController::class, 'destroy'])
            ->name('inventory.destroy');
    });


    // Pengajuan Barang Keluar
    // Admin → membuat pengajuan
    // Validator → melihat dan memvalidasi
    Route::middleware('role:admin')->group(function () {

        Route::get('/inventory/out', [PersediaanController::class, 'createOut'])
            ->name('inventory.out.create');

        Route::post('/inventory/out', [PersediaanController::class, 'storeOut'])
            ->name('inventory.out.store');
    });

    Route::middleware('role:admin,validator')->group(function () {

        Route::get('/inventory/pengajuan', [PersediaanController::class, 'pengajuan'])
            ->name('inventory.pengajuan');
    });

    // Approve / Reject Pengajuan Barang Keluar → Validator
    Route::middleware('role:validator')->group(function () {

        Route::patch('/inventory/{id}/approve', [PersediaanController::class, 'approve'])
            ->name('inventory.approve');

        Route::patch('/inventory/{id}/reject', [PersediaanController::class, 'reject'])
            ->name('inventory.reject');
    });


    // Lihat Persediaan → Admin, Validator, Pimpinan
    Route::middleware('role:admin,validator,pimpinan')->group(function () {

        Route::get('/inventory', [PersediaanController::class, 'index'])
            ->name('inventory.index');

        Route::get('/inventory/{id}', [PersediaanController::class, 'show'])
            ->name('inventory.show');
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 4. OPNAME FISIK
    // ─────────────────────────────────────────────────────────────────────────

    // Membuat sesi & input hasil fisik → Admin
    Route::middleware('role:admin')->group(function () {

        Route::get('/opname/create', [OpnameController::class, 'create'])
            ->name('opname.create');

        Route::post('/opname', [OpnameController::class, 'store'])
            ->name('opname.store');

        Route::get('/opname/{id}/input-fisik', [OpnameController::class, 'inputFisik'])
            ->name('opname.input_fisik');

        Route::post('/opname/{id}/save-fisik', [OpnameController::class, 'saveFisik'])
            ->name('opname.save_fisik');
    });


    // Approve / Reject Opname → Validator
    Route::middleware('role:validator')->group(function () {

        Route::post('/opname/{id}/approve', [OpnameController::class, 'approve'])
            ->name('opname.approve');

        Route::post('/opname/{id}/reject', [OpnameController::class, 'reject'])
            ->name('opname.reject');
    });


    // Lihat Opname → Admin, Validator, Pimpinan
    Route::middleware('role:admin,validator,pimpinan')->group(function () {

        Route::get('/opname', [OpnameController::class, 'index'])
            ->name('opname.index');

        Route::get('/opname/{id}', [OpnameController::class, 'show'])
            ->name('opname.show');
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 5. LAPORAN & EXPORT
    // Admin & Pimpinan
    // ─────────────────────────────────────────────────────────────────────────

    Route::middleware('role:admin,pimpinan')->group(function () {

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/aset/pdf', [ReportController::class, 'exportAsetPdf'])
            ->name('reports.aset.pdf');

        Route::get('/reports/aset/excel', [ReportController::class, 'exportAsetExcel'])
            ->name('reports.aset.excel');

        Route::get('/reports/persediaan/pdf', [ReportController::class, 'exportPersediaanPdf'])
            ->name('reports.persediaan.pdf');

        Route::get('/reports/persediaan/excel', [ReportController::class, 'exportPersediaanExcel'])
            ->name('reports.persediaan.excel');

        Route::get('/reports/opname/pdf', [ReportController::class, 'exportOpnamePdf'])
            ->name('reports.opname.pdf');
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 6. PROFIL & NOTIFIKASI
    // Semua role yang sudah login
    // ─────────────────────────────────────────────────────────────────────────

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


    // ─────────────────────────────────────────────────────────────────────────
    // 7. IMPORT & SINKRONISASI SIMAN / SAKTI
    // Admin
    // ─────────────────────────────────────────────────────────────────────────

    Route::middleware('role:admin,operator')->group(function () {

        Route::get('/import', [\App\Http\Controllers\ImportController::class, 'index'])
            ->name('import.index');

        Route::post('/import', [\App\Http\Controllers\ImportController::class, 'importAuto'])
            ->name('import.auto');

        Route::post('/import/sakti', [\App\Http\Controllers\ImportController::class, 'importSakti'])
            ->name('import.sakti');

        Route::post('/import/siman', [\App\Http\Controllers\ImportController::class, 'importSiman'])
            ->name('import.siman');

        Route::get('/import/template/{type}', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])
            ->name('import.template');
    });

    // ─────────────────────────────────────────────────────────────────────────
    // 7.1 MODUL UPLOAD & VALIDASI DATA (STAGING & PROMOSI)
    // ─────────────────────────────────────────────────────────────────────────

    // Melihat paket & baris (Admin, Operator, Validator, Pimpinan)
    Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
        Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
        Route::get('/upload/{id}', [UploadController::class, 'show'])->name('upload.show');
    });

    // Mengunggah file & revisi (Admin & Operator)
    Route::middleware('role:admin,operator')->group(function () {
        Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
        Route::post('/upload/{id}/revisi', [UploadController::class, 'revisi'])->name('upload.revisi');
    });

    // Validasi per baris & setujui semua (Validator & Admin)
    Route::middleware('role:validator,admin')->group(function () {
        Route::post('/upload/baris/{barisId}/setujui', [UploadController::class, 'setujuiBaris'])->name('upload.baris.setujui');
        Route::post('/upload/baris/{barisId}/tolak', [UploadController::class, 'tolakBaris'])->name('upload.baris.tolak');
        Route::post('/upload/{id}/setujui-semua', [UploadController::class, 'setujuiSemua'])->name('upload.setujui_semua');
    });


    // ─────────────────────────────────────────────────────────────────────────
    // 8. PENGATURAN SISTEM
    // Admin
    // ─────────────────────────────────────────────────────────────────────────

    Route::middleware('role:admin')->group(function () {

        Route::get('/settings', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::post('/settings', [SettingController::class, 'update'])
            ->name('settings.update');

        Route::get('/settings/backup', [SettingController::class, 'backup'])
            ->name('settings.backup');
    });

});