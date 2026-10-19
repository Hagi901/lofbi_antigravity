# Arsitektur Aplikasi LOFBI

Dokumen ini menjelaskan arsitektur perangkat lunak LOFBI (*Layanan Operasional & Form BMN / Inventarisasi*) pada KSOP Kelas I Banten. Aplikasi dibangun menggunakan arsitektur pragmatis Laravel: Controller tipis (*thin controller*), logika bisnis terpusat pada Service layer, dan persistensi data pada Model Eloquent.

---

## 1. Pola Arsitektur Utama (Controller -> Service -> Model)

Aplikasi LOFBI menerapkan pemisahan tanggung jawab yang tegas:
- **Controller**: Mengelola validasi request input, otorisasi peran pengguna (*CheckRole* middleware), memanggil Service layer terkait, dan mengembalikan respons HTTP (Blade View atau JSON Resource).
- **Service Layer**: Mengenkapsulasi seluruh aturan bisnis aplikasi (transaksi database multi-tabel, antrean status, kalkulasi penyusutan aset, pemotongan stok FIFO, dan audit log). Service dapat dipanggil bergantian oleh Web Controller maupun API Controller.
- **Model (Eloquent)**: Representasi skema data, relasi antar-entitas, scope pencarian, dan casting tipe data.

```mermaid
flowchart TD
    subgraph Client ["Klien / Pengguna"]
        WebBrowser["Web Browser (Blade UI)"]
        ApiClient["REST Client / Mobile App"]
    end

    subgraph HttpLayer ["HTTP Layer"]
        Router["Route Modules (routes/web/*, routes/api/*)"]
        Middleware["Middleware (auth, role:CheckRole)"]
        WebCtrl["Web Controllers (app/Http/Controllers/Web/*)"]
        ApiCtrl["Api Controllers (app/Http/Controllers/Api/*)"]
    end

    subgraph ServiceLayer ["Business Logic Service Layer"]
        UploadSvc["UploadService & Parsers (app/Services/Upload/*)"]
        PengajuanSvc["PengajuanService (app/Services/Persediaan/*)"]
        OpnameSvc["OpnameService (app/Services/Opname/*)"]
        ReportSvc["ExcelBuilder & Reports (app/Services/Report/*)"]
    end

    subgraph DataLayer ["Data Access & Storage"]
        Models["Eloquent Models (app/Models/*)"]
        Database[("Database Relasional (MySQL / SQLite)")]
        Storage[("Storage Berkas (storage/app/*)")]
    end

    WebBrowser -->|HTTP Request / Session| Router
    ApiClient -->|Bearer Token Sanctum| Router

    Router --> Middleware
    Middleware --> WebCtrl
    Middleware --> ApiCtrl

    WebCtrl -->|Panggil Logika Bisnis| UploadSvc
    WebCtrl -->|Panggil Logika Bisnis| PengajuanSvc
    WebCtrl -->|Panggil Logika Bisnis| OpnameSvc
    WebCtrl -->|Unduh Laporan| ReportSvc

    ApiCtrl -->|Panggil Logika Bisnis| PengajuanSvc
    ApiCtrl -->|Panggil Logika Bisnis| OpnameSvc
    ApiCtrl -->|Panggil Logika Bisnis| ReportSvc

    UploadSvc --> Models
    PengajuanSvc --> Models
    OpnameSvc --> Models
    ReportSvc --> Models

    Models --> Database
    UploadSvc --> Storage
```

---

## 2. Alur Unggah Staging dan Validasi Satu Pintu

Seluruh data persediaan bersumber dari berkas eksternal resmi (format Excel SAKTI `bahan_opname_fisik.xlsx`). Tidak ada pengisian manual di luar mekanisme ini.

```mermaid
sequenceDiagram
    autonumber
    actor AdminOp as Administrator / Operator
    participant UploadCtrl as Web UploadController
    participant Parser as SaktiStagingParser / XlsxParser
    participant UploadSvc as UploadService
    participant DB as Basis Data (MySQL / SQLite)
    actor Validator as Validator
    participant Notif as Sistem Notifikasi

    AdminOp->>UploadCtrl: Unggah berkas Excel SAKTI (.xlsx)
    UploadCtrl->>UploadSvc: tanganiUnggah(file, user, ruanganId)
    UploadSvc->>Parser: Ekstraksi baris, deteksi placeholder & saldo
    Parser-->>UploadSvc: Kumpulan baris terstruktur
    UploadSvc->>DB: Buat UploadPaket (status: menunggu) & UploadBaris
    UploadSvc->>Notif: Kirim notifikasi 'upload_baru' ke seluruh Validator
    UploadCtrl-->>AdminOp: Redirect ke halaman rincian paket staging

    Note over Validator, DB: Pemeriksaan per baris hanya boleh oleh Validator
    Validator->>UploadCtrl: Setujui baris atau Tolak baris (wajib alasan)
    UploadCtrl->>UploadSvc: setujuiBaris() / tolakBaris()

    alt Disetujui
        UploadSvc->>DB: Update baris -> disetujui
        UploadSvc->>DB: Promosikan ke Persediaan & BatchPersediaan resmi
        UploadSvc->>Notif: Kirim notifikasi 'disetujui' ke pengunggah
    else Ditolak
        UploadSvc->>DB: Update baris -> ditolak (simpan deskripsi_revisi)
        UploadSvc->>Notif: Kirim notifikasi 'ada_ditolak' ke pengunggah
        AdminOp->>UploadCtrl: Unggah berkas revisi (POST /upload/{paket}/revisi)
        UploadCtrl->>UploadSvc: prosesRevisi()
        UploadSvc->>DB: Gantikan baris ditolak tanpa menyentuh baris disetujui
    end
```

---

## 3. Peta Modul Aplikasi

Aplikasi terbagi ke dalam modul modular yang saling terisolasi:

```mermaid
graph TD
    subgraph ModulUtama ["Modul Utama LOFBI"]
        M1["1. Autentikasi & Profil"]
        M2["2. Dashboard & Statistik"]
        M3["3. Aset BMN"]
        M4["4. Persediaan & FIFO"]
        M5["5. Stok Opname Fisik"]
        M6["6. Upload Staging & Validasi"]
        M7["7. Laporan & Ekspor"]
        M8["8. Notifikasi & Audit Log"]
        M9["9. Pengaturan & Backup"]
    end

    M1 --> M2
    M6 -->|Membuat Master & Batch| M4
    M4 -->|Pengajuan Keluar| M8
    M5 -->|Penyesuaian Stok| M4
    M3 -->|Laporan Aset| M7
    M4 -->|Laporan Persediaan| M7
    M5 -->|Laporan BAOP| M7
    M9 -->|Preferensi Instansi| M2
```

### Rincian Modul
1. **Autentikasi & Profil (`app/Http/Controllers/Web/Auth/`, `app/Http/Controllers/Web/ProfileController.php`)**: Autentikasi sesi berbasis cookie untuk Web dan Bearer Token Laravel Sanctum untuk REST API.
2. **Dashboard (`app/Http/Controllers/Web/DashboardController.php`, `Api/DashboardController.php`)**: Agregasi metrik total aset, nilai perolehan, total saldo persediaan, peringatan stok menipis, dan antrean pengajuan.
3. **Aset BMN (`app/Http/Controllers/Web/AsetController.php`, `Api/AsetController.php`)**: Pengelolaan inventaris fisik BMN, nomor register/NUP, lokasi ruangan, kondisi barang, dan perhitungan nilai susut semesteran otomatis.
4. **Persediaan & FIFO (`app/Services/Persediaan/PengajuanService.php`)**: Tata kelola barang habis pakai, pemecahan per batch kedatangan, pengajuan barang keluar, serta algoritma pemotongan stok otomatis metode First In First Out.
5. **Stok Opname Fisik (`app/Services/Opname/OpnameService.php`)**: Pembukaan sesi opname per periode/ruangan, snapshot stok buku, pencatatan hasil hitung fisik lapangan, perhitungan selisih, dan persetujuan penyesuaian otomatis.
6. **Upload Staging & Validasi (`app/Services/Upload/`)**: Mekanisme gerbang satu pintu impor data bersumber dari berkas eksternal dengan tinjauan mandiri oleh Validator.
7. **Laporan & Ekspor (`app/Services/Report/ExcelBuilder.php`, `Web/ReportController.php`, `Api/LaporanController.php`)**: Penyusunan laporan resmi Berita Acara Opname Fisik (BAOP), Daftar Barang Ruangan (DBR), Nilai Buku Aset, serta ekspor file CSV/Excel RFC 4180 dan PDF DomPDF.
8. **Notifikasi Drawer & Audit Trail (`Web/NotificationController.php`, `Api/MonitoringController.php`, `Api/AuditController.php`)**: Riwayat aksi pengguna ke tabel `audit_logs` dan sistem notifikasi internal berbasis drawer dengan penandaan baca otomatis.
9. **Pengaturan & Backup (`Web/SettingController.php`, `Api/SettingsController.php`)**: Konfigurasi nama satuan kerja KSOP, format tanggal, dan pembuatan cadangan database.
