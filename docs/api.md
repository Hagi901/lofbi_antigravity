# Dokumentasi REST API LOFBI

Dokumentasi antarmuka REST API LOFBI (*Layanan Operasional & Form BMN / Inventarisasi*) KSOP Kelas I Banten berbasis Laravel 12 Sanctum.

- **Base URL**: `http://127.0.0.1:8000/api`
- **Header Autentikasi**: `Authorization: Bearer {token}`
- **Header Permintaan**: `Accept: application/json`, `Content-Type: application/json`
- **Matriks Akses Lengkap**: Lihat [Matriks Peran (docs/role.md)](role.md)

---

## 1. Autentikasi & Akun

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `POST` | `/api/login` | Publik | Autentikasi email dan password, mengembalikan token Sanctum. |
| `POST` | `/api/logout` | Semua peran | Revoke personal access token yang sedang aktif. |
| `GET` | `/api/me` | Semua peran | Mengambil data akun pengguna yang sedang login. |

**Contoh Payload Login**:
```json
{
  "email": "admin@lofbi.test",
  "password": "password"
}
```

---

## 2. Dashboard & Monitoring

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `GET` | `/api/dashboard/summary` | Semua peran | Ringkasan metrik total aset, persediaan, nilai buku, dan alert. |
| `GET` | `/api/monitoring/tracking` | Semua peran | Tracking lokasi aset terkini dan tanggal pembaruan. |
| `GET` | `/api/monitoring/peringatan-opname` | Semua peran | Daftar aset yang belum dilakukan opname fisik lebih dari 6 bulan. |
| `GET` | `/api/monitoring/log-aktivitas` | Semua peran | Daftar 15 aktivitas transaksi terbaru pada sistem. |

---

## 3. Manajemen Aset BMN

Input awal data aset terpusat melalui modul unggah staging berkas eksternal. Endpoint API aset melayani pencarian, pembaruan, dan pelaporan unit.

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `GET` | `/api/aset/ringkas` | Semua peran | Rekap aset per jenis barang (paginasi 20 item). Query: `kategori_id`, `kondisi`, `ruangan_id`, `search`. |
| `GET` | `/api/aset/jenis/{jenisBarang}/unit` | Semua peran | Daftar seluruh unit aset pada satu kelompok jenis barang. |
| `GET` | `/api/aset/{aset}` | Semua peran | Rincian lengkap aset beserta nilai buku dinamis dan riwayat mutasi. |
| `PUT` | `/api/aset/{aset}` | Admin, Operator | Pembaruan data aset (kondisi, ruangan, penanggung jawab, nilai). |
| `DELETE`| `/api/aset/{aset}` | Admin, Operator | Menghapus data unit aset (HTTP 204 No Content). |
| `GET` | `/api/aset/{aset}/riwayat` | Semua peran | Riwayat mutasi lokasi atau kondisi aset. |
| `GET` | `/api/aset/{aset}/qr` | Semua peran | Payload metadata untuk pencetakan label QR Code aset. |

---

## 4. Persediaan & Pemotongan FIFO

Seluruh saldo persediaan bersumber dari berkas resmi SAKTI yang disetujui. Pengeluaran barang diproses melalui antrean persetujuan dengan kalkulasi FIFO otomatis.

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `GET` | `/api/persediaan/ringkas` | Semua peran | Ringkasan stok persediaan dikelompokkan per jenis barang. |
| `GET` | `/api/persediaan/jenis/{jenisBarang}/detail` | Semua peran | Daftar varian persediaan beserta batch aktifnya. |
| `GET` | `/api/persediaan/{persediaan}/batch` | Semua peran | Riwayat batch masuk pada satu master persediaan. |
| `GET` | `/api/persediaan/{persediaan}` | Semua peran | Rincian data master persediaan. |
| `PUT` | `/api/persediaan/{persediaan}` | Admin, Operator | Pembaruan data master persediaan (merk, satuan, stok minimum). |
| `POST`| `/api/persediaan/{persediaan}/pengajuan-keluar` | Admin, Operator | Pengajuan pengeluaran barang (validasi stok fisik di awal). |
| `GET` | `/api/persediaan/pengajuan` | Admin, Validator | Daftar antrean pengajuan pengeluaran barang keluar. |
| `POST`| `/api/persediaan/pengajuan/{transaksi}/setujui` | Validator | Persetujuan pengeluaran dan pemotongan stok otomatis FIFO. |
| `POST`| `/api/persediaan/pengajuan/{transaksi}/tolak` | Validator | Penolakan pengeluaran barang (wajib `catatan_penolakan`). |

---

## 5. Stok Opname Fisik

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `GET` | `/api/opname/ruangan/{ruangan}` | Admin, Operator, Validator | Referensi daftar aset dan persediaan per ruangan untuk pengisian fisik. |
| `POST`| `/api/opname` | Admin, Operator | Simpan hasil sesi opname fisik beserta rincian item. |
| `GET` | `/api/opname/riwayat` | Admin, Operator, Validator | Riwayat sesi opname fisik (paginasi 15 item). |

---

## 6. Laporan & Ekspor Data

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `GET` | `/api/laporan/baop` | Semua peran | Data Laporan Berita Acara Opname Fisik. |
| `GET` | `/api/laporan/dbr` | Semua peran | Data Laporan Daftar Barang Ruangan (filter: `ruangan_id`). |
| `GET` | `/api/laporan/nilai-buku` | Semua peran | Data Rekapitulasi Nilai Perolehan dan Nilai Buku Aset. |
| `GET` | `/api/laporan/export` | Semua peran | Ekspor data berkas (Query: `jenis=baop|dbr|nilai-buku`, `format=csv|json|excel`). |

---

## 7. Pengaturan & Master Data

| Method | Endpoint | Akses Peran | Keterangan |
|---|---|---|---|
| `GET` | `/api/audit-trail` | Admin | Log aktivitas audit trail seluruh transaksi aplikasi. |
| `GET` | `/api/settings` | Admin | Informasi profil instansi KSOP dan preferensi sistem. |
| `POST`| `/api/settings` | Admin | Simpan perubahan konfigurasi instansi. |
| `POST`| `/api/backup` | Admin | Eksekusi pencadangan database sistem. |
| `GET` | `/api/ruangan` | Semua peran | Referensi daftar ruangan aktif. |
| `GET` | `/api/kategori` | Semua peran | Referensi kategori aset dan persediaan. |

---

## 8. Koleksi Postman

Koleksi pengujian Postman siap pakai tersedia pada direktori:
[`docs/postman/LOFBI_API.postman_collection.json`](postman/LOFBI_API.postman_collection.json)
