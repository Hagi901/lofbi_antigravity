# LOFBI REST API Specification
### Dokumentasi API Backend (Laravel 12 Sanctum) - KSOP Kelas I Banten

**Base URL**: `http://127.0.0.1:8000/api`  
**Authentication Header**: `Authorization: Bearer {sanctum_token}`

---

## 👥 Matriks Hak Akses Peran (Role-Based Access Control)

| Modul & Endpoint | Admin | Operator | Validator | Pimpinan |
|---|:---:|:---:|:---:|:---:|
| Dashboard (`GET /api/dashboard/*`) | Ya | Ya | Ya | Ya |
| Lihat Data Aset (`GET /api/aset/*`) | Ya | Ya | Ya | Ya |
| Edit & Hapus Aset (`PUT/DELETE /api/aset/*`) | Ya | Ya | Tidak | Tidak |
| Lihat Persediaan (`GET /api/persediaan/*`) | Ya | Ya | Ya | Ya |
| Edit Master Persediaan (`PUT /api/persediaan/*`) | Ya | Ya | Tidak | Tidak |
| Buat Pengajuan Keluar (`POST /api/persediaan/*/pengajuan-keluar`) | Ya | Ya | Tidak | Tidak |
| Lihat Daftar Pengajuan (`GET /api/persediaan/pengajuan`) | Ya | Tidak | Ya | Tidak |
| Validasi Pengajuan Keluar (`POST /api/persediaan/pengajuan/*/setujui|tolak`) | Ya | Tidak | Ya | Tidak |
| Buat Sesi Opname (`POST /api/opname`) | Ya | Ya | Tidak | Tidak |
| Lihat Riwayat Opname (`GET /api/opname/*`) | Ya | Ya | Ya | Tidak |
| Monitoring & Laporan (`GET /api/monitoring/*`, `GET /api/laporan/*`) | Ya | Ya | Ya | Ya |
| Pengaturan & Backup Sistem (`/api/settings`, `/api/backup`, `/api/audit-trail`) | Ya | Tidak | Tidak | Tidak |

---

## 🔐 1. Autentikasi & User Profile

### `POST /api/login`
- **Public** (Tidak perlu token)
- **Body Request**:
  ```json
  {
    "email": "admin@lofbi.test",
    "password": "password"
  }
  ```
- **Response Success (200 OK)**:
  ```json
  {
    "token": "1|sanctum_token_string_here",
    "user": {
      "id": 1,
      "name": "Admin LOFBI",
      "email": "admin@lofbi.test",
      "role": "admin"
    }
  }
  ```

### `POST /api/logout`
- **Protected** (`auth:sanctum`)
- **Response Success (200 OK)**: `{"message": "Logged out successfully"}`

### `GET /api/me`
- **Protected** (`auth:sanctum`)
- **Response Success (200 OK)**: Menampilkan objek user yang sedang login.

---

## 📦 2. Modul Manajemen Aset (KBM & BMN)

> **Catatan Alur Masuk Data**: Input manual aset (`POST /api/aset`) telah ditiadakan sesuai tata kelola satu pintu. Seluruh data aset masuk melalui modul Upload Berkas Staging (SIMAN) dan divalidasi oleh Validator sebelum disetujui masuk ke master data resmi.

### `GET /api/aset/ringkas`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Query Params**: `kategori_id`, `kondisi`, `ruangan_id`, `search`, `page`
- **Response**: Ringkasan daftar aset dikelompokkan per jenis barang (paginasi 20).

### `GET /api/aset/jenis/{jenisBarang}/unit`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: List unit aset per jenis barang.

### `GET /api/aset/{aset}`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Detail informasi aset beserta penyusutan & riwayat mutasi.

### `PUT /api/aset/{aset}`
- **Protected** (`role:admin,operator`)
- **Response**: Update data aset.

### `DELETE /api/aset/{aset}`
- **Protected** (`role:admin,operator`)
- **Response (204 No Content)**.

### `GET /api/aset/{aset}/riwayat`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Riwayat mutasi/perpindahan lokasi & kondisi aset.

### `GET /api/aset/{aset}/qr`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Payload QR Code aset (Kode Aset, Lokasi, Kondisi, Metadata).

---

## 🗃️ 3. Modul Persediaan & Batch FIFO

> **Catatan Alur Masuk Data**: Input manual persediaan dan barang masuk manual (`POST /api/persediaan`, `POST /api/persediaan/{id}/barang-masuk`, `POST /api/persediaan/transfer-masuk`) telah ditiadakan. Seluruh data saldo/batch persediaan bersumber dari berkas resmi SAKTI (`bahan_opname_fisik.xlsx`) melalui modul Upload Berkas Staging, diperiksa per baris oleh Validator, dan dipromosikan otomatis ke batch persediaan berstatus disetujui.

### `GET /api/persediaan/ringkas`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Ringkasan stok persediaan per jenis barang.

### `GET /api/persediaan/jenis/{jenisBarang}/detail`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Detail persediaan beserta daftar batch stoknya.

### `GET /api/persediaan/{persediaan}/batch`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Daftar riwayat batch masuk persediaan (diurutkan FIFO).

### `GET /api/persediaan/{persediaan}`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Detail satu master persediaan.

### `PUT /api/persediaan/{persediaan}`
- **Protected** (`role:admin,operator`)
- **Response**: Update informasi master persediaan.

### `POST /api/persediaan/{persediaan}/pengajuan-keluar`
- **Protected** (`role:admin,operator`)
- **Body Request**: `{"jumlah": 10, "tanggal": "2026-08-10", "unit_kerja_penerima": "Seksi Kepegawaian"}`
- **Response (201 Created)**: Membuat transaksi pengajuan berstatus `menunggu`.

### `GET /api/persediaan/pengajuan`
- **Protected** (`role:admin,validator`)
- **Query Params**: `status` (`menunggu`, `disetujui`, `ditolak`)
- **Response**: List pengajuan barang keluar.

### `POST /api/persediaan/pengajuan/{transaksi}/setujui`
- **Protected** (`role:validator,admin`)
- **Action**: Pemotongan stok otomatis secara **FIFO (First-In, First-Out)** dari batch tertua.

### `POST /api/persediaan/pengajuan/{transaksi}/tolak`
- **Protected** (`role:validator,admin`)
- **Body Request**: `{"catatan_penolakan": "Alasan penolakan"}`

---

## 📡 4. Modul Monitoring Aset Real-Time

### `GET /api/monitoring/tracking`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Query Params**: `ruangan_id`, `kondisi`, `page`
- **Response**: Real-time tracking lokasi aset & update timestamps terakhir.

### `GET /api/monitoring/peringatan-opname`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Jumlah & daftar aset yang belum di-opname > 6 bulan (`last_opname_date <= NOW - 6 Months`).

### `GET /api/monitoring/log-aktivitas`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: 15 log aktivitas sistem terbaru.

---

## 📜 5. Modul Audit Trail

### `GET /api/audit-trail`
- **Protected** (`role:admin`)
- **Query Params**: `modul`, `aksi`, `search`, `page`
- **Response**: Rekam jejak log transaksi sistem lengkap.

---

## ⚙️ 6. Modul Settings & System Preferences

### `GET /api/settings`
- **Protected** (`role:admin`)
- **Response**: Ambil profil instansi (Nama KSOP, Alamat, Logo) & preferensi sistem.

### `POST /api/settings`
- **Protected** (`role:admin`)
- **Body Request**: `{"nama_ksop": "KSOP Kelas I Banten", "format_tanggal": "DD MMM YYYY", "tahun_anggaran": "2026"}`

### `POST /api/backup`
- **Protected** (`role:admin`)
- **Response**: Response status backup data database.

---

## 📋 7. Modul Opname Fisik & Laporan

### `GET /api/opname/ruangan/{ruangan}`
- **Protected** (`role:admin,operator,validator`)
- **Response**: Ambil daftar barang di ruangan untuk checklist opname.

### `POST /api/opname`
- **Protected** (`role:admin,operator`)
- **Body Request**: Buat sesi opname fisik & detail rekonsiliasi.

### `GET /api/opname/riwayat`
- **Protected** (`role:admin,operator,validator`)
- **Response**: Riwayat sesi opname.

### `GET /api/laporan/baop`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Laporan Berita Acara Opname Fisik (BAOP).

### `GET /api/laporan/dbr`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Laporan Daftar Barang Ruangan (DBR).

### `GET /api/laporan/nilai-buku`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Rekap Nilai Buku & Penyusutan.

### `GET /api/laporan/export`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Query Params**: `jenis` (`baop`, `dbr`, `nilai-buku`), `format` (`json`, `csv`, `excel`)
- **Response**: Download file CSV/Excel RFC 4180 atau JSON payload.

### `GET /api/dashboard/summary`
- **Protected** (`role:admin,operator,validator,pimpinan`)
- **Response**: Ringkasan data statistik untuk Dashboard.
