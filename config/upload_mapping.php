<?php

/**
 * Konfigurasi pemetaan kolom dan sheet untuk setiap jenis file upload LOFBI.
 *
 * Semua konfigurasi parser ada di sini - jangan hardcode di dalam kelas Service.
 * Untuk menambah jenis file baru, tambahkan key baru di array ini.
 */

return [

    // -------------------------------------------------------------------------
    // SAKTI: Bahan Opname Fisik (bahan_opname_fisik.xlsx)
    // Sheet: "Data master" (semua barang) dan "input LOFBI" (saldo > 0)
    // -------------------------------------------------------------------------
    'sakti_opname' => [
        'sheets' => [
            'data_master' => 'Data master',   // ~842 baris
            'input_lofbi' => 'input LOFBI',   // ~76 baris, saldo > 0
        ],

        // Sheet default yang diproses saat upload (sheet "input LOFBI" = data aktif)
        'default_sheet' => 'input LOFBI',

        // Kata kunci untuk mendeteksi baris header
        // Parser akan mencari baris yang mengandung semua kata kunci ini
        'header_keywords' => [
            'nama' => ['nama barang', 'nama_barang'],
            'kode' => ['kode barang', 'kode_barang', 'kode'],
        ],

        // Pemetaan nama kolom header (lowercase) ke field internal
        // Key = field internal, Value = array kata kunci yang mungkin muncul di header
        'column_map' => [
            'kode_barang' => ['kode barang', 'kode_barang', 'kode'],
            'nama_barang' => ['nama barang', 'nama_barang', 'nama'],
            'satuan' => ['satuan'],
            'saldo_sistem' => ['saldo di sistem', 'saldo sistem', 'saldo', 'stok'],
            'hasil_opsik' => ['hasil opsik', 'hasil opname', 'hasil fisik'],
            'kategori' => ['kategori'],
            'tgl_perolehan' => ['tanggal perolehan', 'tgl perolehan', 'tgl_perolehan'],
        ],

        // Baris nomor kolom yang harus di-skip (baris berisi "1", "2", "3", ...)
        // Parser akan deteksi otomatis baris yang semua isinya angka kecil berurutan
        'skip_column_number_rows' => true,

        // Nilai nama_barang dan satuan yang menandakan placeholder
        'placeholder_values' => ['-', '--', 'n/a', 'none', ''],

        // Panjang kode barang yang valid (16 digit)
        'kode_barang_length' => 16,
    ],

    // -------------------------------------------------------------------------
    // SAKTI: Rincian Buku Persediaan
    // CATATAN: Hanya tersedia sebagai Word/docx - FORMAT TIDAK DIDUKUNG.
    // Jika file Excel atau PDF-teks asli tersedia, konfigurasi di sini.
    // -------------------------------------------------------------------------
    'sakti_rincian_buku' => [
        'status' => 'pending',
        'keterangan' => 'Format Word (.docx) tidak didukung. Tunggu file Excel atau PDF teks asli.',
    ],

    // -------------------------------------------------------------------------
    // SIMAN: Data Aset BMN
    // CATATAN: File sampel belum tersedia di storage/samples/. PENDING.
    // -------------------------------------------------------------------------
    'siman' => [
        'status' => 'pending',
        'keterangan' => 'File sampel SIMAN belum tersedia. Stage 5 ditunda.',
    ],

    // -------------------------------------------------------------------------
    // Ekstensi file yang diizinkan untuk upload
    // -------------------------------------------------------------------------
    'allowed_extensions' => ['xlsx', 'csv'],

    // Ekstensi yang ditolak dengan pesan jelas
    'rejected_extensions' => [
        'docx' => 'File Word (.docx) tidak didukung. Gunakan file Excel (.xlsx) atau CSV.',
        'doc' => 'File Word (.doc) tidak didukung. Gunakan file Excel (.xlsx) atau CSV.',
        'pdf' => 'Upload PDF belum didukung di tahap ini. Gunakan file Excel (.xlsx).',
    ],

    // Batas ukuran file upload (dalam KB)
    'max_file_size_kb' => 10240, // 10 MB

];
