LOFBI — Laporan Opname Fisik Barang & Inventarisasi
Aplikasi Internal KSOP Kelas I Banten, Kementerian Perhubungan RI

Tampilkan Gambar Tampilkan Gambar Tampilkan Gambar

LOFBI (Laporan Opname Fisik Barang & Inventarisasi) adalah aplikasi manajemen aset inventaris dan persediaan barang milik negara (BMN) yang dirancang khusus untuk memenuhi kebutuhan operasional Kantor Kesyahbandaran dan Otoritas Pelabuhan (KSOP) Kelas I Banten.

Status saat ini: repo ini baru berisi prototipe frontend statis (HTML/CSS/JS, data dummy). Backend Laravel 12 belum dikembangkan/di-push — lihat bagian Struktur Direktori Proyek dan Roadmap.

🚀 Fitur Utama & Modul Sistem

Sistem terdiri dari 11 Modul Lengkap:

Dashboard Overview: Ringkasan statistik real-time (Total Aset, Nilai Buku, Pengajuan Menunggu, Stok Menipis, Aset Rusak, Kondisi Baik), Grafik Tren Kondisi Aset (Chart.js), Distribusi Persediaan, & Quick Action.
Manajemen Aset (KBM & BMN): Pendataan aset inventaris lengkap dengan Sub Kategori, Masa Manfaat (Tahun), Akumulasi Penyusutan (Rp), Nilai Buku, Metode Penyusutan (Garis Lurus & Saldo Menurun), Riwayat Mutasi, dan Upload Dokumen.
Persediaan & Batch Stok (FIFO): Manajemen persediaan barang konsumsi dengan pelacakan nomor batch masuk (Nomor Referensi, Nomor Faktur, Nota Dinas, Nama Supplier, Tanggal Masuk), stok minimum, dan kalkulasi pemotongan stok FIFO.
Opname Fisik Barang: Pencocokan jumlah stok sistem dengan fisik barang di lapangan per ruangan, pembuatan Berita Acara Opname Fisik (BAOP), dan status sesi opname.
Monitoring Aset Real-Time: Tracking lokasi real-time aset, peringatan otomatis aset yang belum di-opname > 6 bulan, serta log aktivitas sistem terkini.
Laporan BAOP & DBR: Generasi dan cetak laporan resmi Berita Acara Opname Fisik (BAOP), Daftar Barang Ruangan (DBR), Rekap Nilai Buku, serta fitur export data PDF/Excel.
Audit Trail Log Transaksi: Rekam jejak transaksional seluruh perubahan data di modul aset, persediaan, opname, dan persetujuan (Timestamp, User, Modul, Aksi, Detail).
Approval Pengajuan Barang Keluar: Verifikasi dan keputusan persetujuan barang keluar oleh pihak berwenang dengan histori keputusan penolakan/persetujuan.
Master Data Referensi: Pengelolaan data referensi Ruangan dan Kategori Barang.
Manajemen Pengguna (User Accounts): Manajemen pengguna dengan otorisasi 5 Role System.
Pengaturan Sistem (Settings): Profil Instansi (Nama KSOP, Alamat, Logo), Preferensi Format Tanggal & Tahun Anggaran, Role & Permission, dan Fitur Backup Data.
👥 Simulasi 5 Hak Akses / Role Users

Sistem mendukung 5 Hak Akses (Role) dengan tingkat otorisasi yang disesuaikan:

Role	Akun Email	Password	Hak Akses & Permission
Administrator	admin@lofbi.test	password	Akses penuh seluruh modul (CRUD Aset, Persediaan, Opname, Users, Settings)
Operator	operator@lofbi.test	password	Input & update operasional (Tambah Aset, Catat Barang Masuk, Monitoring)
Validator	validator@lofbi.test	password	Verifikasi & validasi kondisi aset, hasil opname, serta persetujuan barang
Viewer	viewer@lofbi.test	password	Hak akses Read-Only (Tombol Aksi Tambah/Edit/Hapus/Approve disembunyikan otomatis)
Pimpinan	pimpinan@lofbi.test	password	Review laporan, monitoring audit trail, dan persetujuan tingkat pimpinan
📁 Struktur Direktori Proyek

Struktur repo saat ini (prototipe frontend statis):

text
lofbi_antigravity/
├── index.html                        <-- Standalone Static SPA Prototype (HTML/CSS/JS)
└── public/                           <-- Assets publik (CSS & JS API Bridge)
    ├── css/lofbi.css                 <-- Design System & Styling Kemenhub UI
    ├── js/lofbi.js                   <-- Frontend Application Logic & SPA Router
    └── js/lofbi-api.js               <-- Sanctum REST API Bridge (siap dipakai begitu backend tersedia)

Backend Laravel 12 (backend_ref/) direncanakan sebagai pasangan proyek ini tapi belum diimplementasikan/di-push ke repo ini. lofbi-api.js sudah disiapkan untuk terhubung ke endpoint REST API Laravel di http://127.0.0.1:8000/api, namun untuk saat ini seluruh data yang tampil di index.html masih data dummy hardcoded.

💻 Cara Menjalankan Proyek (Panduan Instalasi)
Menjalankan Prototipe Frontend (index.html)

Cukup buka file index.html langsung di browser favorit Anda (Chrome/Firefox/Edge). Tidak memerlukan web server atau database tambahan — semua data yang tampil masih dummy/statis.

🗺️ Roadmap
 Implementasi backend Laravel 12 (backend_ref/): Models, Controllers, Migrations, Seeders, REST API
 Hubungkan index.html ke backend via lofbi-api.js (ganti data dummy dengan data nyata)
 Autentikasi Sanctum sungguhan menggantikan simulasi ganti role di frontend
 Implementasi generator laporan BAOP/DBR (PDF/Excel) di sisi backend
📄 Lisensi & Hak Cipta

© 2026 KSOP Kelas I Banten — Kementerian Perhubungan Republik Indonesia.
Pelayanan, Integritas, Keselamatan.