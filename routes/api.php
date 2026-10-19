<?php

use App\Http\Controllers\Api\AsetController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\OpnameController;
use App\Http\Controllers\Api\PersediaanController;
use App\Http\Controllers\Api\SettingsController;
use Illuminate\Support\Facades\Route;


// ==========================================================================
// AUTHENTICATION
// ==========================================================================

// Login - public
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1');


// ==========================================================================
// ROUTE TERLINDUNGI - WAJIB LOGIN
// ==========================================================================

Route::middleware('auth:sanctum')->group(function () {

    // ======================================================================
    // AUTH
    // ======================================================================

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);


    // ======================================================================
    // ASET
    // Admin      : Edit & Hapus
    // Operator   : Lihat
    // Validator  : Lihat
    // Pimpinan   : Lihat
    // Viewer     : Lihat (read-only)
    // ======================================================================

    // Semua role authenticated dapat melihat data Aset
    Route::middleware('role:admin,operator,validator,pimpinan,viewer')->group(function () {

        Route::get('/aset/ringkas', [AsetController::class, 'ringkas']);

        Route::get(
            '/aset/jenis/{jenisBarang}/unit',
            [AsetController::class, 'unit']
        );

        Route::get('/aset/{aset}', [AsetController::class, 'show']);

        Route::get(
            '/aset/{aset}/riwayat',
            [AsetController::class, 'riwayat']
        );

        Route::get(
            '/aset/{aset}/qr',
            [AsetController::class, 'qr']
        );
    });

    // Edit & Hapus Aset -> Admin
    Route::middleware('role:admin')->group(function () {

        Route::put('/aset/{aset}', [AsetController::class, 'update']);

        Route::patch('/aset/{aset}', [AsetController::class, 'update']);

        Route::delete('/aset/{aset}', [AsetController::class, 'destroy']);
    });


    // ======================================================================
    // PERSEDIAAN
    // Admin      : Edit + pengajuan-keluar
    // Operator   : Lihat + pengajuan-keluar
    // Validator  : Lihat + validasi pengajuan keluar
    // Pimpinan   : Lihat
    // Viewer     : Lihat (read-only)
    // ======================================================================

    // Semua role authenticated dapat melihat Persediaan
    Route::middleware('role:admin,operator,validator,pimpinan,viewer')->group(function () {

        Route::get(
            '/persediaan/ringkas',
            [PersediaanController::class, 'ringkas']
        );

        Route::get(
            '/persediaan/jenis/{jenisBarang}/detail',
            [PersediaanController::class, 'detailByJenis']
        );

        Route::get(
            '/persediaan/{persediaan}/batch',
            [PersediaanController::class, 'batch']
        );

        Route::get(
            '/persediaan/{persediaan}',
            [PersediaanController::class, 'show']
        );
    });


    // ======================================================================
    // PENGAJUAN BARANG KELUAR - Lihat
    // Admin + Operator + Validator dapat melihat
    // ======================================================================

    Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {

        Route::get(
            '/persediaan/pengajuan',
            [PersediaanController::class, 'pengajuan']
        );
    });


    // ======================================================================
    // OPERASIONAL PERSEDIAAN
    // Admin + Operator - buat pengajuan keluar
    // Admin - edit master persediaan
    // ======================================================================

    // Edit master persediaan -> Admin
    Route::middleware('role:admin')->group(function () {

        Route::put(
            '/persediaan/{persediaan}',
            [PersediaanController::class, 'update']
        );

        Route::patch(
            '/persediaan/{persediaan}',
            [PersediaanController::class, 'update']
        );
    });

    // Pengajuan Barang Keluar -> Admin + Operator
    Route::middleware('role:admin,operator')->group(function () {

        Route::post(
            '/persediaan/{persediaan}/pengajuan-keluar',
            [PersediaanController::class, 'pengajuanKeluar']
        );
    });


    // ======================================================================
    // VALIDASI PENGAJUAN BARANG KELUAR
    // Validator + Admin (sesuai web)
    // ======================================================================

    Route::middleware('role:validator,admin')->group(function () {

        Route::post(
            '/persediaan/pengajuan/{transaksi}/setujui',
            [PersediaanController::class, 'setujui']
        );

        Route::post(
            '/persediaan/pengajuan/{transaksi}/tolak',
            [PersediaanController::class, 'tolak']
        );
    });


    // ======================================================================
    // MONITORING
    // Admin + Validator + Pimpinan
    // ======================================================================

    Route::middleware('role:admin,validator,pimpinan')->group(function () {

        Route::get(
            '/monitoring/tracking',
            [MonitoringController::class, 'tracking']
        );

        Route::get(
            '/monitoring/peringatan-opname',
            [MonitoringController::class, 'peringatanOpname']
        );

        Route::get(
            '/monitoring/log-aktivitas',
            [MonitoringController::class, 'logAktivitas']
        );
    });


    // ======================================================================
    // AUDIT TRAIL
    // Admin
    // ======================================================================

    Route::middleware('role:admin')->group(function () {

        Route::get(
            '/audit-trail',
            [AuditController::class, 'index']
        );
    });


    // ======================================================================
    // SETTINGS & BACKUP
    // Admin
    // ======================================================================

    Route::middleware('role:admin')->group(function () {

        Route::get(
            '/settings',
            [SettingsController::class, 'index']
        );

        Route::post(
            '/settings',
            [SettingsController::class, 'store']
        );

        Route::post(
            '/backup',
            [SettingsController::class, 'backup']
        );
    });


    // ======================================================================
    // OPNAME
    // ======================================================================

    // Membuat Opname -> Admin + Operator
    Route::middleware('role:admin,operator')->group(function () {

        Route::post(
            '/opname',
            [OpnameController::class, 'store']
        );
    });


    // Melihat Opname -> Admin, Operator, Validator, Pimpinan
    Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {

        Route::get(
            '/opname/riwayat',
            [OpnameController::class, 'riwayat']
        );

        Route::get(
            '/opname/ruangan/{ruangan}',
            [OpnameController::class, 'ruangan']
        );
    });


    // ======================================================================
    // LAPORAN
    // Admin + Validator + Pimpinan
    // ======================================================================

    Route::middleware('role:admin,validator,pimpinan')->group(function () {

        Route::get(
            '/laporan/baop',
            [LaporanController::class, 'baop']
        );

        Route::get(
            '/laporan/dbr',
            [LaporanController::class, 'dbr']
        );

        Route::get(
            '/laporan/nilai-buku',
            [LaporanController::class, 'nilaiBuku']
        );

        Route::get(
            '/laporan/export',
            [LaporanController::class, 'export']
        );
    });


    // ======================================================================
    // DASHBOARD
    // Admin + Validator + Pimpinan
    // ======================================================================

    Route::middleware('role:admin,validator,pimpinan')->group(function () {

        Route::get(
            '/dashboard/summary',
            [DashboardController::class, 'summary']
        );
    });


    // ======================================================================
    // MASTER DATA
    // Admin, Operator, Validator, Pimpinan -> lihat
    // ======================================================================

    Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {

        Route::get(
            '/ruangan',
            [MasterDataController::class, 'ruangan']
        );

        Route::get(
            '/kategori',
            [MasterDataController::class, 'kategori']
        );
    });

});