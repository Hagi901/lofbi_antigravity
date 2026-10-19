<?php

use App\Http\Controllers\Api\MonitoringController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Monitoring
|--------------------------------------------------------------------------
|
| Matriks Akses:
| - Monitoring & Log: admin, operator, validator, pimpinan
|
*/

Route::middleware('role:admin,operator,validator,pimpinan')->group(function () {
    Route::get('/monitoring/tracking', [MonitoringController::class, 'tracking']);
    Route::get('/monitoring/peringatan-opname', [MonitoringController::class, 'peringatanOpname']);
    Route::get('/monitoring/log-aktivitas', [MonitoringController::class, 'logAktivitas']);
});
