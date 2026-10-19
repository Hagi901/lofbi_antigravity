<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - LOFBI Fullstack Web Interface
|--------------------------------------------------------------------------
|
| Matriks Hak Akses (3 Aktor):
|
| 1. Administrator (role: admin & operator)
|    - Hak sama untuk:
|      * Dashboard (lihat)
|      * Aset & Persediaan (lihat, edit, hapus)
|      * Pengajuan barang keluar (ajukan)
|      * Opname fisik (buat sesi, input fisik, lihat)
|      * Upload & revisi berkas staging, lihat paket & baris
|      * Laporan & ekspor (lihat, unduh)
|      * Profil & notifikasi
|    - Khusus role admin:
|      * Pengaturan sistem & unduh backup (/settings, /settings/backup)
|      * Melihat daftar pengajuan barang keluar
|
| 2. Validator (role: validator)
|    - Dashboard (lihat)
|    - Aset & Persediaan (lihat daftar & detail)
|    - Pengajuan barang keluar (lihat daftar, setujui, tolak)
|    - Opname fisik (lihat sesi/detail, setujui, tolak)
|    - Upload berkas staging (satu-satunya yang berhak setujui, tolak)
|    - Laporan & ekspor (lihat, unduh)
|    - Profil & notifikasi
|
| 3. Pimpinan (role: pimpinan)
|    - Dashboard (lihat)
|    - Aset & Persediaan (lihat daftar & detail)
|    - Laporan & ekspor (lihat, unduh)
|    - Profil & notifikasi
|    - Dilarang (403): /upload, /opname, pengajuan barang keluar, dan semua aksi mutasi data
|
*/

// Rute Autentikasi Publik & Profil/Notifikasi
require __DIR__ . '/web/auth.php';

// Rute Terlindungi - Wajib Login
Route::middleware(['auth'])->group(function () {
    require __DIR__ . '/web/dashboard.php';
    require __DIR__ . '/web/aset.php';
    require __DIR__ . '/web/persediaan.php';
    require __DIR__ . '/web/opname.php';
    require __DIR__ . '/web/upload.php';
    require __DIR__ . '/web/laporan.php';
    require __DIR__ . '/web/pengaturan.php';
});