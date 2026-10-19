<?php

use App\Http\Controllers\Web\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Laporan & Ekspor Data
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Laporan & Ekspor: admin, operator, validator, pimpinan (lihat/unduh)
|
*/

Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
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
