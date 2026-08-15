# LOFBI — Laporan Opname Fisik Barang & Inventarisasi
### Aplikasi Internal KSOP Kelas I Banten, Kementerian Perhubungan RI

[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-blue.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-Internal-green.svg)](#)

LOFBI (Laporan Opname Fisik Barang & Inventarisasi) adalah aplikasi manajemen aset inventaris dan persediaan barang milik negara (BMN) yang dirancang khusus untuk memenuhi kebutuhan operasional Kantor Kesyahbandaran dan Otoritas Pelabuhan (KSOP) Kelas I Banten.

---

## 🚀 Fitur Utama & Modul Sistem

Sistem terdiri dari **11 Modul Lengkap**:

1. **Dashboard Overview**: Ringkasan statistik real-time (Total Aset, Nilai Buku, Pengajuan Menunggu, Stok Menipis, Aset Rusak, Kondisi Baik), Grafik Tren Kondisi Aset (Chart.js), Distribusi Persediaan, & Quick Action.
2. **Manajemen Aset (KBM & BMN)**: Pendataan aset inventaris lengkap dengan Sub Kategori, Masa Manfaat (Tahun), Akumulasi Penyusutan (Rp), Nilai Buku, Metode Penyusutan (Garis Lurus & Saldo Menurun), Riwayat Mutasi, dan Upload Dokumen.
3. **Persediaan & Batch Stok (FIFO)**: Manajemen persediaan barang konsumsi dengan pelacakan nomor batch masuk (Nomor Referensi, Nomor Faktur, Nota Dinas, Nama Supplier, Tanggal Masuk), stok minimum, dan kalkulasi pemotongan stok FIFO.
4. **Opname Fisik Barang**: Pencocokan jumlah stok sistem dengan fisik barang di lapangan per ruangan, pembuatan Berita Acara Opname Fisik (BAOP), dan status sesi opname.
5. **Monitoring Aset Real-Time**: Tracking lokasi real-time aset, peringatan otomatis aset yang belum di-opname > 6 bulan, serta log aktivitas sistem terkini.
6. **Laporan BAOP & DBR**: Generasi dan cetak laporan resmi Berita Acara Opname Fisik (BAOP), Daftar Barang Ruangan (DBR), Rekap Nilai Buku, serta fitur export data PDF/Excel.
7. **Audit Trail Log Transaksi**: Rekam jejak transaksional seluruh perubahan data di modul aset, persediaan, opname, dan persetujuan (Timestamp, User, Modul, Aksi, Detail).
8. **Approval Pengajuan Barang Keluar**: Verifikasi dan keputusan persetujuan barang keluar oleh pihak berwenang dengan histori keputusan penolakan/persetujuan.
9. **Master Data Referensi**: Pengelolaan data referensi Ruangan dan Kategori Barang.
10. **Manajemen Pengguna (User Accounts)**: Manajemen pengguna dengan otorisasi 5 Role System.
11. **Pengaturan Sistem (Settings)**: Profil Instansi (Nama KSOP, Alamat, Logo), Preferensi Format Tanggal & Tahun Anggaran, Role & Permission, dan Fitur Backup Data.

---

## 👥 Simulasi 5 Hak Akses / Role Users

Sistem mendukung **5 Hak Akses (Role)** dengan tingkat otorisasi yang disesuaikan:

| Role | Akun Email | Password | Hak Akses & Permission |
|------|------------|----------|------------------------|
| **Administrator** | `admin@lofbi.test` | `password` | Akses penuh seluruh modul (CRUD Aset, Persediaan, Opname, Users, Settings) |
| **Operator** | `operator@lofbi.test` | `password` | Input & update operasional (Tambah Aset, Catat Barang Masuk, Monitoring) |
| **Validator** | `validator@lofbi.test` | `password` | Verifikasi & validasi kondisi aset, hasil opname, serta persetujuan barang |
| **Viewer** | `viewer@lofbi.test` | `password` | Hak akses *Read-Only* (Tombol Aksi Tambah/Edit/Hapus/Approve disembunyikan otomatis) |
| **Pimpinan** | `pimpinan@lofbi.test` | `password` | Review laporan, monitoring audit trail, dan persetujuan tingkat pimpinan |

---

## 📁 Struktur Direktori Proyek

Proyek ini menyediakan 2 varian implementasi:

```text
lofbi_antigravity/
├── index.html                        <-- [Varian 1] Standalone Static SPA Prototype (HTML/CSS/JS)
├── public/                           <-- Assets publik (CSS & JS API Bridge)
│   ├── css/lofbi.css                 <-- Design System & Styling Kemenhub UI
│   ├── js/lofbi.js                   <-- Frontend Application Logic & SPA Router
│   └── js/lofbi-api.js               <-- Sanctum REST API Bridge
│
└── backend_ref/                      <-- [Varian 2] Aplikasi Utuh Laravel 12 Framework
    ├── app/                          <-- Models, Controllers (API & Web), Form Requests, Middleware
    ├── database/                     <-- Migrations (Tabel Aset, Batch, AuditLog, Settings) & Seeders
    ├── routes/                       <-- api.php (28 Endpoints REST API) & web.php
    ├── resources/views/              <-- 25 Blade Templates Modular (Layouts, Partials, Pages, Modals)
    │   ├── layouts/app.blade.php
    │   ├── partials/ (sidebar, topbar, states, footer)
    │   ├── pages/ (11 halaman modul)
    │   └── modals/ (9 modal dialog)
    └── LOFBI_API.postman_collection.json <-- Collection Postman untuk pengujian API
```

---

## 💻 Cara Menjalankan Proyek (Panduan Instalasi)

### Menjalankan Varian 1: Static SPA (`index.html`)
Cukup buka file `index.html` langsung di browser favorit Anda (Chrome/Firefox/Edge). Tidak memerlukan web server atau database tambahan.

### Menjalankan Varian 2: Backend Laravel 12 (`backend_ref`)

1. Masuk ke direktori `backend_ref`:
   ```bash
   cd backend_ref
   ```
2. Pastikan file `.env` sudah terkonfigurasi. (File `.env` bawaan menggunakan SQLite database).
3. Jalankan database migration & seeder data awal:
   ```bash
   php artisan migrate:fresh --seed
   ```
4. Jalankan server lokal Laravel:
   ```bash
   php artisan serve
   ```
5. Buka di browser: `http://127.0.0.1:8000`

---

## 📄 Lisensi & Hak Cipta

© 2026 **KSOP Kelas I Banten** — Kementerian Perhubungan Republik Indonesia.  
*Pelayanan, Integritas, Keselamatan.*
