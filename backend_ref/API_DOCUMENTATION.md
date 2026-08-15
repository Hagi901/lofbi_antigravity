# LOFBI REST API Specification
### Dokumentasi API Backend (Laravel 12 Sanctum) — KSOP Kelas I Banten

**Base URL**: `http://127.0.0.1:8000/api`  
**Authentication Header**: `Authorization: Bearer {sanctum_token}`

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

### `GET /api/aset/ringkas`
- **Query Params**: `kategori_id`, `kondisi`, `ruangan_id`, `search`, `page`
- **Response**: Ringkasan daftar aset dikelompokkan per jenis barang (paginasi 20).

### `GET /api/aset/jenis/{jenisBarang}/unit`
- **Response**: List unit aset per jenis barang.

### `POST /api/aset`
- **Protected** (`auth:sanctum`)
- **Body Request**:
  ```json
  {
    "kode_aset": "ELK-LAP-003",
    "jenis_barang_id": 1,
    "sub_kategori": "Elektronik",
    "merk": "Dell",
    "model": "Latitude 3420",
    "kondisi": "baik",
    "ruangan_id": 1,
    "nilai_perolehan": 15000000,
    "tanggal_perolehan": "2026-08-01",
    "masa_manfaat": 5,
    "metode_penyusutan": "Garis Lurus"
  }
  ```
- **Response (201 Created)**: Objek aset baru beserta relasinya.

### `GET /api/aset/{aset}`
- **Response**: Detail informasi aset beserta penyusutan & riwayat mutasi.

### `PUT /api/aset/{aset}`
- **Response**: Update data aset.

### `DELETE /api/aset/{aset}`
- **Response (204 No Content)**.

### `GET /api/aset/{aset}/riwayat`
- **Response**: Riwayat mutasi/perpindahan lokasi & kondisi aset.

### `GET /api/aset/{aset}/qr`
- **Response**: Payload QR Code aset (Kode Aset, Lokasi, Kondisi, Metadata).

---

## 🗃️ 3. Modul Persediaan & Batch FIFO

### `GET /api/persediaan/ringkas`
- **Response**: Ringkasan stok persediaan per jenis barang.

### `GET /api/persediaan/jenis/{jenisBarang}/detail`
- **Response**: Detail persediaan beserta daftar batch stoknya.

### `POST /api/persediaan/{persediaan}/barang-masuk`
- **Body Request**:
  ```json
  {
    "no_referensi": "REF-2026-001",
    "no_faktur": "INV-2026-089",
    "nota_dinas": "ND/2026/08",
    "supplier": "Sinar Dunia",
    "tanggal": "2026-08-10",
    "jumlah": 50,
    "harga_satuan": 45000
  }
  ```
- **Response (201 Created)**: Mengenerate nomor batch baru dan menambah sisa stok.

### `POST /api/persediaan/{persediaan}/pengajuan-keluar`
- **Body Request**: `{"jumlah": 10, "tanggal": "2026-08-10", "unit_kerja_penerima": "Seksi Kepegawaian"}`
- **Response (201 Created)**: Membuat transaksi pengajuan berstatus `menunggu`.

### `POST /api/persediaan/transfer-masuk`
- **Body Request**:
  ```json
  {
    "ruangan_asal_id": 2,
    "ruangan_tujuan_id": 1,
    "persediaan_id": 1,
    "jumlah": 20,
    "catatan": "Mutasi barang untuk ruang TU"
  }
  ```

### `GET /api/persediaan/pengajuan`
- **Query Params**: `status` (`menunggu`, `disetujui`, `ditolak`)
- **Response**: List pengajuan barang keluar.

### `POST /api/persediaan/pengajuan/{transaksi}/setujui`
- **Protected** (`role:kasubbag,admin,validator,pimpinan`)
- **Action**: Pemotongan stok otomatis secara **FIFO (First-In, First-Out)** dari batch tertua.

### `POST /api/persediaan/pengajuan/{transaksi}/tolak`
- **Protected** (`role:kasubbag,admin,validator,pimpinan`)
- **Body Request**: `{"catatan_penolakan": "Alasan penolakan"}`

### `GET /api/persediaan/{persediaan}/batch`
- **Response**: Daftar riwayat batch masuk persediaan (diurutkan FIFO).

---

## 📡 4. Modul Monitoring Aset Real-Time

### `GET /api/monitoring/tracking`
- **Query Params**: `ruangan_id`, `kondisi`, `page`
- **Response**: Real-time tracking lokasi aset & update timestamps terakhir.

### `GET /api/monitoring/peringatan-opname`
- **Response**: Jumlah & daftar aset yang belum di-opname > 6 bulan (`last_opname_date <= NOW - 6 Months`).

### `GET /api/monitoring/log-aktivitas`
- **Response**: 15 log aktivitas sistem terbaru.

---

## 📜 5. Modul Audit Trail

### `GET /api/audit-trail`
- **Query Params**: `modul`, `aksi`, `search`, `page`
- **Response**: Rekam jejak log transaksi sistem lengkap.

---

## ⚙️ 6. Modul Settings & System Preferences

### `GET /api/settings`
- **Response**: Ambil profil instansi (Nama KSOP, Alamat, Logo) & preferensi sistem.

### `POST /api/settings`
- **Body Request**: `{"nama_ksop": "KSOP Kelas I Banten", "format_tanggal": "DD MMM YYYY", "tahun_anggaran": "2026"}`

### `POST /api/backup`
- **Response**: Response status backup data database.

---

## 📋 7. Modul Opname Fisik & Laporan

### `GET /api/opname/ruangan/{ruangan}`
- **Response**: Ambil daftar barang di ruangan untuk checklist opname.

### `POST /api/opname`
- **Body Request**: Buat sesi opname fisik & detail rekonsiliasi.

### `GET /api/opname/riwayat`
- **Response**: Riwayat sesi opname.

### `GET /api/laporan/baop`
- **Response**: Laporan Berita Acara Opname Fisik (BAOP).

### `GET /api/laporan/dbr`
- **Response**: Laporan Daftar Barang Ruangan (DBR).

### `GET /api/laporan/nilai-buku`
- **Response**: Rekap Nilai Buku & Penyusutan.

### `GET /api/laporan/export`
- **Query Params**: `jenis` (`baop`, `dbr`, `nilai-buku`), `format` (`json`, `csv`, `excel`)
- **Response**: Download file CSV/Excel RFC 4180 atau JSON payload.

### `GET /api/dashboard/summary`
- **Response**: Ringkasan data statistik untuk Dashboard.
