# Matriks Hak Akses Peran (Role-Based Access Control)

Dokumen ini memuat matriks lengkap izin akses pengguna pada aplikasi LOFBI (*Layanan Operasional & Form BMN / Inventarisasi*) KSOP Kelas I Banten.

---

## 1. Definisi Tiga Aktor Utama

1. **Administrator (`admin` & `operator`)**:
   - Menjalankan seluruh operasional harian: mengelola data aset dan persediaan (lihat, ubah, hapus), membuat pengajuan barang keluar, membuka sesi stok opname dan menginput hasil hitung fisik lapangan, serta mengunggah dan merevisi berkas staging.
   - Pembeda: Khusus akun dengan peran `admin` memiliki akses eksklusif ke menu Pengaturan Sistem (`/settings`), Backup Data Database (`/backup`), dan Audit Trail (`/api/audit-trail`). Role `operator` dibatasi (HTTP 403) dari kedua fitur konfigurasi tersebut.
   - Administrator dilarang menyetujui atau menolak baris unggahan staging demi prinsip pemisahan tugas (*segregation of duties*).

2. **Validator (`validator`)**:
   - Bertindak sebagai pihak verifikator independen (*second line of defense*).
   - Merupakan satu-satunya pihak yang berwenang memvalidasi (menyetujui atau menolak) baris berkas staging pada modul Upload Berkas.
   - Berwenang menyetujui atau menolak pengajuan pengeluaran barang persediaan (eksekusi pemotongan FIFO otomatis).
   - Berwenang memeriksa dan menyetujui atau menolak hasil sesi stok opname fisik.
   - Memiliki akses lihat ke dashboard, daftar aset, daftar persediaan, riwayat opname, daftar pengajuan, dan seluruh laporan resmi.

3. **Pimpinan (`pimpinan`)**:
   - Tingkat manajerial eksekutif dengan hak pantau penuh (*read-only*).
   - Memiliki akses melihat ringkasan dashboard eksekutif, katalog data aset, katalog persediaan, dan seluruh laporan resmi (BAOP, DBR, Nilai Buku, dan unduhan ekspor).
   - Dilarang keras (HTTP 403) dari modul Upload Berkas, modul Stok Opname, pembuatan pengajuan barang keluar, serta seluruh aksi manipulasi atau mutasi data (tambah, ubah, hapus, persetujuan).

---

## 2. Matriks Akses Antarmuka Web

| Modul | URI Route | Admin | Operator | Validator | Pimpinan | Keterangan |
|---|---|:---:|:---:|:---:|:---:|---|
| **Dashboard** | `GET /` | Ya | Ya | Ya | Ya | Ringkasan statistik operasional dan grafik ringkas. |
| **Aset BMN** | `GET /assets` | Ya | Ya | Ya | Ya | Daftar inventaris barang milik negara. |
| | `GET /assets/{id}` | Ya | Ya | Ya | Ya | Detail barang, riwayat mutasi, dan penyusutan. |
| | `GET /assets/{id}/edit` | Ya | Ya | Tidak | Tidak | Form ubah data inventaris aset. |
| | `POST /assets/{id}` | Ya | Ya | Tidak | Tidak | Simpan perubahan data aset. |
| | `DELETE /assets/{id}` | Ya | Ya | Tidak | Tidak | Hapus data inventaris aset. |
| **Persediaan** | `GET /inventory` | Ya | Ya | Ya | Ya | Katalog master persediaan dan total stok. |
| | `GET /inventory/{id}` | Ya | Ya | Ya | Ya | Kartu persediaan dan riwayat batch masuk. |
| | `GET /inventory/{id}/edit` | Ya | Ya | Tidak | Tidak | Form ubah master persediaan. |
| | `POST /inventory/{id}` | Ya | Ya | Tidak | Tidak | Simpan perubahan master persediaan. |
| | `DELETE /inventory/{id}` | Ya | Ya | Tidak | Tidak | Hapus master persediaan dan batch. |
| **Pengajuan Keluar** | `GET /inventory/out` | Ya | Ya | Tidak | Tidak | Form pengajuan pengeluaran barang. |
| | `POST /inventory/out` | Ya | Ya | Tidak | Tidak | Submit transaksi barang keluar (status: menunggu). |
| | `GET /inventory/pengajuan` | Ya | Tidak | Ya | Tidak | Antrean daftar pengajuan keluar menunggu validasi. |
| | `POST /inventory/pengajuan/{id}/approve` | Tidak | Tidak | Ya | Tidak | Setujui pengeluaran dan potong batch FIFO. |
| | `POST /inventory/pengajuan/{id}/reject` | Tidak | Tidak | Ya | Tidak | Tolak pengajuan keluar dengan alasan. |
| **Stok Opname** | `GET /opname` | Ya | Ya | Ya | Tidak | Daftar seluruh sesi stok opname fisik. |
| | `GET /opname/create` | Ya | Ya | Tidak | Tidak | Form inisiasi sesi opname baru. |
| | `POST /opname` | Ya | Ya | Tidak | Tidak | Buka sesi opname baru dan buat snapshot stok buku. |
| | `GET /opname/{id}` | Ya | Ya | Ya | Tidak | Rincian komparasi fisik vs buku per sesi. |
| | `GET /opname/{id}/input-fisik` | Ya | Ya | Tidak | Tidak | Halaman input angka hasil hitung fisik. |
| | `POST /opname/{id}/input-fisik` | Ya | Ya | Tidak | Tidak | Simpan hitung fisik dan ajukan ke Validator. |
| | `POST /opname/{id}/approve` | Tidak | Tidak | Ya | Tidak | Setujui opname dan buat jurnal penyesuaian. |
| | `POST /opname/{id}/reject` | Tidak | Tidak | Ya | Tidak | Tolak opname dan kembalikan ke Operator. |
| **Upload Staging** | `GET /upload` | Ya | Ya | Ya | Tidak | Daftar paket unggahan berkas eksternal. |
| | `GET /upload/{id}` | Ya | Ya | Ya | Tidak | Rincian baris data dalam paket staging. |
| | `POST /upload` | Ya | Ya | Tidak | Tidak | Unggah berkas Excel SAKTI (.xlsx). |
| | `POST /upload/{id}/revisi` | Ya | Ya | Tidak | Tidak | Unggah ulang berkas perbaikan baris ditolak. |
| | `POST /upload/baris/{id}/setujui` | Tidak | Tidak | Ya | Tidak | Setujui baris dan promosikan ke batch resmi. |
| | `POST /upload/baris/{id}/tolak` | Tidak | Tidak | Ya | Tidak | Tolak baris (wajib menyertakan alasan). |
| | `POST /upload/{id}/setujui-semua` | Tidak | Tidak | Ya | Tidak | Setujui seluruh baris pending dalam satu paket. |
| **Laporan & Ekspor** | `GET /reports` | Ya | Ya | Ya | Ya | Halaman pusat unduh laporan instansi. |
| | `GET /reports/dbr/export` | Ya | Ya | Ya | Ya | Unduh Laporan Daftar Barang Ruangan (PDF/Excel). |
| | `GET /reports/persediaan/export` | Ya | Ya | Ya | Ya | Unduh Laporan Posisi Persediaan (PDF/Excel). |
| | `GET /reports/opname/{id}/export` | Ya | Ya | Ya | Ya | Unduh Berita Acara Opname Fisik (BAOP). |
| **Notifikasi & Profil** | `GET /notifications` | Ya | Ya | Ya | Ya | Drawer riwayat notifikasi internal. |
| | `POST /notifications/mark-all-read` | Ya | Ya | Ya | Ya | Tandai semua notifikasi telah dibaca. |
| | `GET /profile` | Ya | Ya | Ya | Ya | Informasi akun pengguna yang aktif. |
| | `POST /profile` | Ya | Ya | Ya | Ya | Pembaruan data profil dan password pengguna. |
| **Pengaturan & Backup** | `GET /settings` | Ya | Tidak | Tidak | Tidak | Konfigurasi profil instansi dan identitas sistem. |
| | `POST /settings` | Ya | Tidak | Tidak | Tidak | Simpan perubahan konfigurasi sistem. |
| | `GET /settings/backup/download` | Ya | Tidak | Tidak | Tidak | Unduh berkas cadangan database aplikasi. |

---

## 3. Matriks Akses REST API (`/api/*`)

| Modul | Endpoint API | Method | Admin | Operator | Validator | Pimpinan |
|---|---|:---:|:---:|:---:|:---:|:---:|
| **Autentikasi** | `/api/login` | POST | Publik | Publik | Publik | Publik |
| | `/api/logout` | POST | Ya | Ya | Ya | Ya |
| | `/api/me` | GET | Ya | Ya | Ya | Ya |
| **Dashboard** | `/api/dashboard/summary` | GET | Ya | Ya | Ya | Ya |
| **Aset** | `/api/aset/ringkas` | GET | Ya | Ya | Ya | Ya |
| | `/api/aset/jenis/{jenis}/unit` | GET | Ya | Ya | Ya | Ya |
| | `/api/aset/{aset}` | GET | Ya | Ya | Ya | Ya |
| | `/api/aset/{aset}` | PUT | Ya | Ya | Tidak | Tidak |
| | `/api/aset/{aset}` | DELETE | Ya | Ya | Tidak | Tidak |
| | `/api/aset/{aset}/riwayat` | GET | Ya | Ya | Ya | Ya |
| | `/api/aset/{aset}/qr` | GET | Ya | Ya | Ya | Ya |
| **Persediaan** | `/api/persediaan/ringkas` | GET | Ya | Ya | Ya | Ya |
| | `/api/persediaan/jenis/{jenis}/detail` | GET | Ya | Ya | Ya | Ya |
| | `/api/persediaan/{persediaan}/batch` | GET | Ya | Ya | Ya | Ya |
| | `/api/persediaan/{persediaan}` | GET | Ya | Ya | Ya | Ya |
| | `/api/persediaan/{persediaan}` | PUT | Ya | Ya | Tidak | Tidak |
| | `/api/persediaan/{persediaan}/pengajuan-keluar` | POST | Ya | Ya | Tidak | Tidak |
| | `/api/persediaan/pengajuan` | GET | Ya | Tidak | Ya | Tidak |
| | `/api/persediaan/pengajuan/{transaksi}/setujui` | POST | Tidak | Tidak | Ya | Tidak |
| | `/api/persediaan/pengajuan/{transaksi}/tolak` | POST | Tidak | Tidak | Ya | Tidak |
| **Stok Opname** | `/api/opname/ruangan/{ruangan}` | GET | Ya | Ya | Ya | Tidak |
| | `/api/opname` | POST | Ya | Ya | Tidak | Tidak |
| | `/api/opname/riwayat` | GET | Ya | Ya | Ya | Tidak |
| **Monitoring** | `/api/monitoring/tracking` | GET | Ya | Ya | Ya | Ya |
| | `/api/monitoring/peringatan-opname` | GET | Ya | Ya | Ya | Ya |
| | `/api/monitoring/log-aktivitas` | GET | Ya | Ya | Ya | Ya |
| **Laporan** | `/api/laporan/baop` | GET | Ya | Ya | Ya | Ya |
| | `/api/laporan/dbr` | GET | Ya | Ya | Ya | Ya |
| | `/api/laporan/nilai-buku` | GET | Ya | Ya | Ya | Ya |
| | `/api/laporan/export` | GET | Ya | Ya | Ya | Ya |
| **Audit Trail** | `/api/audit-trail` | GET | Ya | Tidak | Tidak | Tidak |
| **Pengaturan** | `/api/settings` | GET | Ya | Tidak | Tidak | Tidak |
| | `/api/settings` | POST | Ya | Tidak | Tidak | Tidak |
| | `/api/backup` | POST | Ya | Tidak | Tidak | Tidak |
| **Master Data** | `/api/ruangan` | GET | Ya | Ya | Ya | Ya |
| | `/api/kategori` | GET | Ya | Ya | Ya | Ya |
