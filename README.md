# 📦 LOFBI API - REST API Inventaris & Persediaan (BMN)

![Laravel Version](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20SQLite-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Aplikasi **Sistem Layanan Operasional & Form BMN / Inventarisasi (LOFBI)** untuk KSOP Kelas I Banten. Aplikasi ini mengelola aset fisik inventaris (dengan kalkulasi penyusutan otomatis), persediaan barang habis pakai (dengan metode pemotongan stok FIFO), stok opname fisik per ruangan, serta menyajikan ringkasan statistik dan laporan resmi (BAOP, DBR, Nilai Buku).

> Repo ini berisi **dua antarmuka yang berjalan bersamaan** di atas basis data & business logic yang sama:
> 1. **Web app (Blade)** - antarmuka utama untuk dipakai sehari-hari (`routes/web.php`, login sesi biasa di `/login`).
> 2. **REST API (Sanctum)** - untuk integrasi eksternal/mobile (`routes/api.php`, lihat [`API_DOCUMENTATION.md`](API_DOCUMENTATION.md)).

---

## ✨ Fitur Utama

- 🔑 **Autentikasi Bearer Token (Sanctum)** - Keamanan endpoint dengan sistem perizinan berbasis peran (*Role-Based Access Control*).
- 👥 **Aktor Pengguna (Tiga Aktor Utama)**:
  - **Administrator** (`admin` & `operator`): Hak akses operasional setara meliputi pengelolaan aset & persediaan (lihat, edit, hapus), pembuatan pengajuan barang keluar, pelaksanaan opname fisik (buka sesi & input hasil fisik), upload berkas & revisi staging, akses laporan/ekspor, dan dashboard. Khusus role `admin` memiliki akses eksklusif ke pengaturan sistem dan unduh backup data.
  - **Validator** (`validator`): Verifikasi & validasi baris upload staging (setujui/tolak per baris atau sekaligus), persetujuan (*approval*) pengajuan barang keluar (pemotongan stok FIFO), persetujuan opname, serta melihat data aset, persediaan, riwayat opname, laporan, dan dashboard.
  - **Pimpinan** (`pimpinan`): Pemantauan eksekutif (*read-only*) untuk dashboard ringkasan, daftar aset, daftar persediaan, dan laporan resmi. Pimpinan dibatasi secara tegas (HTTP 403) dari modul opname, upload data, pengajuan barang keluar, dan seluruh aksi mutasi data.
- 📂 **Satu Pintu Input Data via Upload Staging & Validasi**:
  - Seluruh data masuk hanya melalui modul **Upload Berkas** (file Excel SAKTI `bahan_opname_fisik.xlsx`).
  - Fitur input manual ("Tambah Master Barang", "Tambah Barang Masuk", dan "Tambah Aset") ditiadakan demi integritas data terpusat.
  - Staging berjenjang: Berkas diunggah sebagai satu paket berstatus `menunggu`. Validator memeriksa per baris dengan opsi tolak (wajib mengisi deskripsi revisi) atau setujui.
  - Baris disetujui langsung dipromosikan ke batch persediaan resmi dalam transaksi basis data aman.
  - Mekanisme revisi: Pengunggah dapat mengunggah ulang berkas perbaikan; baris dengan kunci unik yang sama menggantikan baris ditolak tanpa menyentuh atau menduplikasi baris yang sudah disetujui.
  - Format Word (`.docx`) ditolak secara tegas dengan pesan peringatan jelas.
  - **Status Tahap 5 (SIMAN Aset) & Tahap 6 (PDF Berbasis Teks)**: Dicatat sebagai **PENDING** karena sampel berkas resmi belum tersedia di `storage/samples/`.
- 🏢 **Manajemen Aset Inventaris**:
  - Pengelompokan aset per jenis barang dan unit detail.
  - Penghitungan penyusutan metode garis lurus (*straight-line*) otomatis per semester.
- 📦 **Manajemen Persediaan & Batch FIFO**:
  - Pencatatan barang masuk per batch bersumber dari berkas SAKTI yang disetujui.
  - Pemotongan stok otomatis metode **FIFO (First In, First Out)** dari batch paling awal saat disetujui Validator.
  - Indikator badge label *(Nilai belum lengkap)* jika harga satuan batch belum diisi pada format opname fisik.
- 🔔 **Sistem Notifikasi Drawer Terintegrasi**:
  - Ikon lonceng interaktif dengan badge notifikasi belum dibaca.
  - Panel samping (offcanvas drawer) selebar ~50% layar yang otomatis menandai notifikasi telah dibaca saat dibuka.
  - Tombol aksi langsung (CTA) menuju paket staging terfilter atau tabel modul terkait.
- 📋 **Stok Opname Fisik**:
  - Verifikasi kondisi fisik barang aktual per ruangan dan pencatatan riwayat sesi opname.
- 📊 **Dashboard & Laporan**:
  - Statistik total aset, nilai buku, barang rusak, alert stok menipis, dan pengajuan pending.
  - Laporan Berita Acara Opname (BAOP), Daftar Barang Ruangan (DBR), Nilai Buku Aset, serta Export CSV berstandar RFC 4180.

---

## 🛠️ Persyaratan Sistem

- PHP `>= 8.2`
- Composer `>= 2.0`
- MySQL / MariaDB (via XAMPP) atau SQLite
- Extension PHP: `OpenSSL`, `PDO`, `Mbstring`, `Tokenizer`, `XML`, `Ctype`, `JSON`, `BCMath`

---

## 🚀 Panduan Instalasi (Local Development)

### 1. Clone Repository
```bash
git clone https://github.com/Hagi901/lofbi-api.git
cd lofbi-api
```

### 2. Install Dependency PHP
```bash
composer install
```

### 3. Buat File Konfigurasi `.env`
```bash
copy .env.example .env
php artisan key:generate
```

### 4. Konfigurasi Database di `.env` (XAMPP / MySQL)
Buat database bernama **`lofbi_db`** di phpMyAdmin (`http://localhost/phpmyadmin`), lalu buka file `.env` dan atur:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lofbi_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan Migrasi Database & Seeder Data Demo
```bash
php artisan migrate:fresh --seed
```

### 6. Jalankan Server API
```bash
php artisan serve
```
Server REST API aktif di: **`http://127.0.0.1:8000/api`**

---

## 🔐 Akun Demo (Seeder)

Password semua akun demo: `password`

| Role | Email | Hak Akses Utama |
|---|---|---|
| **Admin** | `admin@lofbi.test` | Full akses (Pengaturan & Backup, Upload, Aset, Persediaan, Opname, Pengajuan, Laporan) |
| **Operator** | `operator@lofbi.test` | Upload berkas staging & revisi, kelola aset & persediaan, buat pengajuan keluar, sesi & input opname, laporan |
| **Validator** | `validator@lofbi.test` | Validasi berkas staging (setujui/tolak), approval barang keluar (potong FIFO), approval opname, laporan |
| **Pimpinan** | `pimpinan@lofbi.test` | Read-only eksekutif (Dashboard, Aset, Persediaan, Laporan) |

---

## 📑 Daftar Endpoint Utama (REST API)

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| `POST` | `/api/login` | Public | Login user & dapatkan token Bearer |
| `POST` | `/api/logout` | Auth | Revoke token session |
| `GET` | `/api/me` | Auth | Profil user terautentikasi |
| `GET` | `/api/dashboard/summary` | Auth | Ringkasan statistik & alert dashboard |
| `GET` | `/api/aset/ringkas` | Auth | Ringkasan aset per jenis barang (Paginated) |
| `GET` | `/api/aset/jenis/{id}/unit` | Auth | Detail unit aset per jenis barang |
| `GET` | `/api/persediaan/ringkas` | Auth | Ringkasan persediaan & sisa stok total |
| `POST` | `/api/persediaan/{id}/pengajuan-keluar` | Admin, Operator | Buat pengajuan barang keluar |
| `GET` | `/api/persediaan/pengajuan` | Auth | Daftar pengajuan barang keluar |
| `POST` | `/api/persediaan/pengajuan/{id}/setujui` | Validator, Kasubbag | Approval pengajuan & potong stok FIFO |
| `POST` | `/api/persediaan/pengajuan/{id}/tolak` | Validator, Kasubbag | Tolak pengajuan barang keluar (wajib isi alasan) |
| `GET` | `/api/opname/ruangan/{id}` | Admin, Operator | Referensi aset & persediaan di ruangan |
| `POST` | `/api/opname` | Admin, Operator | Simpan hasil pemeriksaan opname fisik |
| `GET` | `/api/laporan/baop` | Auth | Laporan Berita Acara Opname |
| `GET` | `/api/laporan/dbr` | Auth | Laporan Daftar Barang Ruangan |
| `GET` | `/api/laporan/nilai-buku` | Auth | Laporan Rekap Nilai Buku Aset |
| `GET` | `/api/laporan/export?jenis=dbr&format=csv` | Auth | Export data laporan ke format CSV |

---

## 🧪 Pengujian API (Postman Collection)

File **[`LOFBI_API.postman_collection.json`](LOFBI_API.postman_collection.json)** telah tersedia di root repository ini.

1. Buka aplikasi **Postman** / Bruno / Insomnia.
2. Import file `LOFBI_API.postman_collection.json`.
3. Jalankan request **`Login Admin`** atau **`Login Kasubbag`**. Token Bearer akan otomatis tersimpan untuk request berikutnya.

---

## 📐 Algoritma & Logika Bisnis

### 1. Metode FIFO Persediaan
```text
Batch #1 (Masuk 10 Jan) -> Sisa: 20 pcs @ Rp 3.000
Batch #2 (Masuk 15 Apr) -> Sisa: 100 pcs @ Rp 3.200

Pengajuan Keluar disetujui: 30 pcs
- 20 pcs diambil dari Batch #1 (Sisa Batch #1 -> 0)
- 10 pcs diambil dari Batch #2 (Sisa Batch #2 -> 90)
```

### 2. Penyusutan Aset (Garis Lurus per Semester)
```text
Penyusutan / Tahun = Nilai Perolehan / Masa Manfaat (Tahun)
Penyusutan / Semester = Penyusutan / Tahun / 2
Nilai Buku = Nilai Perolehan - Akumulasi Penyusutan
```
Command scheduler: `php artisan lofbi:hitung-penyusutan` (Otomatis berjalan tiap 1 Jan & 1 Juli).

---

## 🚀 Deploy ke Production

File `.env` untuk development (local) **tidak boleh** dipakai langsung di server production. Sebelum go-live, pastikan checklist ini sudah dijalankan:

- [ ] **`APP_ENV=production`** dan **`APP_DEBUG=false`** - kalau `APP_DEBUG` masih `true`, setiap error akan menampilkan stack trace, path file server, dan query SQL ke siapa pun yang mengakses halaman error. Ini risiko keamanan nyata, bukan sekadar kerapian.
- [ ] **`LOG_LEVEL=error`** (bukan `debug`) - supaya file log tidak cepat membengkak dan tidak mencatat detail yang terlalu rinci.
- [ ] **CORS** (`config/cors.php`) - `'allowed_origins' => ['*']` saat ini sengaja dibuat longgar untuk memudahkan development. Sebelum production, ganti jadi daftar domain spesifik yang benar-benar akan memakai API ini.
- [ ] **Cron scheduler** - command `lofbi:hitung-penyusutan` (penyusutan aset tiap 1 Jan & 1 Jul) hanya berjalan otomatis kalau server punya cron job yang menjalankan `php artisan schedule:run` setiap menit. Ini **tidak otomatis ada** - perlu didaftarkan manual di cron server (lihat [dokumentasi Task Scheduling Laravel](https://laravel.com/docs/scheduling#running-the-scheduler)).
- [ ] **Rate limiting login** - endpoint `/login` (web) dan `/api/login` belum punya pembatasan percobaan login (throttle), rentan brute-force. Tambahkan middleware `throttle` sebelum go-live.
- [ ] **`MYSQLDUMP_PATH`** di `.env` - kalau pakai fitur Backup API dengan MySQL, pastikan path `mysqldump` sudah sesuai lokasi di server production (berbeda antara Windows/XAMPP dan Linux).
- [ ] **Jalankan `php artisan config:cache` & `php artisan route:cache`** setelah `.env` production final - mempercepat response time.

---

## 📄 Lisensi

Project ini dirilis di bawah lisensi [MIT License](LICENSE).
