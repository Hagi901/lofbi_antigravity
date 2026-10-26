# 📦 LOFBI - Layanan Operasional & Form BMN / Inventarisasi

![Laravel Version](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP Version](https://img.shields.io/badge/PHP-%5E8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Database](https://img.shields.io/badge/Database-MySQL%20%7C%20SQLite-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

Aplikasi **LOFBI** (*Layanan Operasional & Form BMN / Inventarisasi*) adalah sistem pengelolaan Barang Milik Negara (BMN) dan persediaan barang habis pakai pada Kantor Kesyahbandaran dan Otoritas Pelabuhan (KSOP) Kelas I Banten. Aplikasi ini menyediakan tata kelola inventaris aset fisik dengan penyusutan dinamis, pengelolaan persediaan dengan pemotongan stok otomatis First In First Out (FIFO), stok opname fisik berbasis ruangan, alur satu pintu impor berkas resmi berjenjang, dan pelaporan operasional terpadu.

Aplikasi menyediakan dua antarmuka terintegrasi yang berjalan berdampingan di atas logika bisnis yang sama:
1. **Web App (Blade)**: Antarmuka operasional harian untuk pengguna kantor melalui browser (`/login`).
2. **REST API (Sanctum)**: Antarmuka terprogram untuk integrasi sistem dan aplikasi seluler (`/api`).

---

## ✨ Fitur Utama

- 🚪 **Gerbang Input Satu Pintu via Upload Staging**: Seluruh data persediaan masuk melalui modul Upload Berkas (file Excel SAKTI `bahan_opname_fisik.xlsx`). Input manual ditiadakan demi integritas data.
- 🛡️ **Validasi Berjenjang oleh Validator**: Berkas diunggah ke tabel staging sebagai satu paket (`menunggu`). HANYA role Validator yang berhak memeriksa, menyetujui, atau menolak per baris (wajib alasan penolakan). Baris disetujui langsung dipromosikan ke batch persediaan resmi.
- 🔄 **Mekanisme Revisi Tanpa Duplikasi**: Pengunggah dapat mengunggah ulang berkas perbaikan untuk baris yang ditolak tanpa menduplikasi baris yang sudah pernah disetujui.
- 📦 **Persediaan & Pemotongan Stok FIFO**: Pencatatan saldo persediaan per nomor batch kedatangan. Pemotongan stok barang keluar dilakukan secara otomatis dari batch paling awal saat disetujui.
- 🏢 **Manajemen Aset BMN & Penyusutan**: Pencatatan kode BMN, NUP, nomor seri, ruangan penempatan, kondisi fisik, dan perhitungan akumulasi penyusutan metode garis lurus per semester.
- 📋 **Stok Opname Fisik**: Pembukaan sesi opname, pembuatan snapshot stok buku, pengisian hasil hitung fisik lapangan, kalkulasi selisih otomatis, dan persetujuan penyesuaian stok.
- 🔔 **Notifikasi Drawer Terintegrasi**: Sistem notifikasi offcanvas drawer dengan penandaan baca otomatis dan tautan aksi langsung ke baris atau pengajuan terkait.
- 📊 **Laporan Resmi & Ekspor Data**: Cetak dan unduh Berita Acara Opname Fisik (BAOP), Daftar Barang Ruangan (DBR), Nilai Buku Aset, serta ekspor file CSV/Excel RFC 4180 dan PDF.

---

## 👥 Tiga Aktor Pengguna & Hak Akses

Aplikasi menerapkan pemisahan tugas (*segregation of duties*) yang ketat dengan tiga peran aktor utama:

1. **Administrator (`admin` & `operator`)**:
   - Memiliki hak operasional penuh: mengelola data aset dan master persediaan (lihat, ubah, hapus), membuat pengajuan barang keluar, membuka sesi opname dan menginput hasil hitung fisik, serta mengunggah berkas staging dan mengirimkan revisi.
   - Pembeda hak: Khusus akun `admin` memiliki akses eksklusif ke Pengaturan Sistem (`/settings`), Backup Database, dan Audit Trail, serta dapat melihat daftar pengajuan barang keluar secara *read-only*. Role `operator` dibatasi (HTTP 403) dari fitur-fitur tersebut.
   - Administrator dan operator dilarang memvalidasi, menyetujui, atau menolak baris unggahan staging, pengajuan barang keluar, maupun sesi opname fisik.
2. **Validator (`validator`)**:
   - Satu-satunya peran yang berwenang menyetujui atau menolak: (1) baris unggahan staging (per baris atau sekaligus), (2) pengajuan pengeluaran barang persediaan (eksekusi pemotongan FIFO), dan (3) hasil sesi stok opname fisik.
   - Memiliki hak lihat pada dashboard, data aset, data persediaan, daftar pengajuan, dan seluruh laporan.
3. **Pimpinan (`pimpinan`)**:
   - Peran pemantauan eksekutif dengan hak baca penuh (*read-only*): dashboard statistik, katalog aset, katalog persediaan, dan seluruh laporan resmi.
   - Dilarang keras (HTTP 403) mengakses modul upload berkas, stok opname, pengajuan keluar, dan seluruh aksi mutasi data.

Matriks izin akses lengkap dapat dibaca pada [Matriks Peran (docs/role.md)](docs/role.md).

---

## 🛠️ Persyaratan Lingkungan

- PHP `>= 8.2`
- Composer `>= 2.2`
- MySQL / MariaDB (XAMPP) atau SQLite
- Ekstensi PHP aktif: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`, `zip`

---

## 🚀 Panduan Instalasi Lokal

### 1. Klon Repositori
```bash
git clone https://github.com/Hagi901/lofbi_antigravity.git
cd lofbi_antigravity
```

### 2. Pasang Dependensi
```bash
composer install
```

### 3. Konfigurasi Lingkungan (`.env`)
```bash
copy .env.example .env
php artisan key:generate
```

Sesuaikan parameter koneksi basis data pada `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lofbi_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder Data
Jalankan migrasi tabel beserta akun demo dan data referensi awal:
```bash
php artisan migrate:fresh --seed
```

### 5. Jalankan Server Aplikasi
```bash
php artisan serve
```
- Antarmuka Web: **`http://127.0.0.1:8000`**
- Antarmuka REST API: **`http://127.0.0.1:8000/api`**

---

## 🔑 Akun Demo Pengujian

Seluruh akun demo menggunakan kata sandi bawaan: **`password`**

| Peran | Alamat Email | Hak Akses Utama |
|---|---|---|
| **Administrator** | `admin@lofbi.test` | Seluruh operasional, upload berkas, pengaturan sistem, dan backup. |
| **Operator** | `operator@lofbi.test` | Seluruh operasional, upload berkas, input fisik opname, ajukan barang. |
| **Validator** | `validator@lofbi.test` | Validasi baris upload, persetujuan FIFO keluar, persetujuan opname. |
| **Pimpinan** | `pimpinan@lofbi.test` | Pemantauan dashboard, aset, persediaan, dan unduh laporan resmi. |

---

## 🧪 Menjalankan Pengujian Otomatis

Aplikasi dilengkapi test suite komprehensif yang mencakup pengujian otentikasi, hak akses per role, pemotongan stok FIFO, upload staging, dan integritas route:

```bash
# Jalankan seluruh test suite
php artisan test

# Format kode otomatis sesuai standar PSR-12 / Laravel
vendor/bin/pint
```

---

## 📁 Struktur Folder Proyek

```
lofbi_antigravity/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                 # REST API Controllers (Sanctum)
│   │   │   └── Web/                 # Web Blade Controllers (Auth, Aset, Opname, dll)
│   │   ├── Middleware/              # CheckRole middleware
│   │   ├── Requests/                # Form Request Validations
│   │   └── Resources/               # API Json Resources
│   ├── Models/                      # Model Eloquent (Aset, Persediaan, Batch, dll)
│   └── Services/                    # Logika Bisnis Terpusat
│       ├── Opname/                  # OpnameService (buka sesi, hitung nilai, approval)
│       ├── Persediaan/              # PengajuanService (pengajuan keluar, FIFO cutting)
│       ├── Report/                  # ExcelBuilder & Report generators
│       └── Upload/                  # UploadService, SaktiStagingParser, XlsxParser
├── config/                          # Konfigurasi aplikasi
├── database/
│   ├── migrations/                  # Skema migrasi tabel
│   └── seeders/                     # Seeder akun demo dan data master
├── docs/                            # Dokumentasi Teknis
│   ├── api.md                       # Spesifikasi ringkas REST API
│   ├── arsitektur.md                # Diagram arsitektur dan peta modul
│   ├── integrasi-frontend.md        # Panduan integrasi UI eksternal
│   ├── role.md                      # Matriks lengkap perizinan peran
│   └── postman/                     # Koleksi Postman LOFBI API
├── resources/
│   └── views/                       # Tampilan antarmuka Blade
│       ├── assets/                  # Tampilan modul Aset
│       ├── inventory/               # Tampilan modul Persediaan
│       ├── layouts/                 # Master layout (sidebar, navbar, drawer)
│       ├── opname/                  # Tampilan modul Stok Opname
│       ├── reports/                 # Tampilan dan template PDF Laporan
│       └── upload/                  # Tampilan antarmuka Upload Staging
├── routes/
│   ├── api/                         # Route modular API per modul
│   ├── web/                         # Route modular Web per modul
│   ├── api.php                      # Endpoint utama API (memuat routes/api/*)
│   └── web.php                      # Endpoint utama Web (memuat routes/web/*)
├── storage/
│   └── samples/                     # Berkas contoh format impor SAKTI / SIMAN
└── tests/
    └── Feature/
        ├── Api/                     # Pengujian fungsional API
        ├── Upload/                  # Pengujian modul Upload Staging & Validasi
        └── Web/                     # Pengujian fungsional antarmuka Web
```

---

## 📚 Tautan Dokumentasi Terkait

- [Arsitektur Sistem & Alur Bisnis (docs/arsitektur.md)](docs/arsitektur.md)
- [Matriks Hak Akses Peran (docs/role.md)](docs/role.md)
- [Dokumentasi REST API (docs/api.md)](docs/api.md)
- [Panduan Integrasi Frontend (docs/integrasi-frontend.md)](docs/integrasi-frontend.md)
- [Koleksi Postman API (docs/postman/)](docs/postman/LOFBI_API.postman_collection.json)
