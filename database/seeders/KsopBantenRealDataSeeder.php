<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\Setting;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Database\Seeder;

class KsopBantenRealDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $gudang = Ruangan::firstOrCreate(['nama' => 'Gudang Persediaan KSOP Banten'], ['gedung' => 'Gedung Operasional']);

        // ── 1. Update Profil Instansi sesuai Dokumen Resmi ─────────────────────
        Setting::updateOrCreate(['key' => 'nama_ksop'], ['value' => 'KANTOR KESYAHBANDARAN DAN OTORITAS PELABUHAN BANTEN']);
        Setting::updateOrCreate(['key' => 'kode_uakpb'], ['value' => '022.04.2900.413670']);
        Setting::updateOrCreate(['key' => 'alamat_instansi'], ['value' => 'Jl. Raya Pelabuhan No. 1, Banten']);
        Setting::updateOrCreate(['key' => 'tahun_anggaran'], ['value' => '2026']);

        // ── 2. Kategori Persediaan Standar BMN ─────────────────────────────────
        $katBBM = Kategori::updateOrCreate(['nama' => 'Bahan Bakar & Pelumas'], ['tipe' => 'persediaan']);
        $katSparepart = Kategori::updateOrCreate(['nama' => 'Suku Cadang & Perlengkapan Kapal'], ['tipe' => 'persediaan']);
        $katATK = Kategori::updateOrCreate(['nama' => 'Alat Tulis Kantor & Cetakan'], ['tipe' => 'persediaan']);
        $katKebersihan = Kategori::updateOrCreate(['nama' => 'Peralatan Kebersihan & Rumah Tangga'], ['tipe' => 'persediaan']);
        $katElektronik = Kategori::updateOrCreate(['nama' => 'Alat & Perlengkapan Komputer'], ['tipe' => 'persediaan']);
        $katP3K = Kategori::updateOrCreate(['nama' => 'Obat-obatan & P3K'], ['tipe' => 'persediaan']);
        $katLogistik = Kategori::updateOrCreate(['nama' => 'Logistik & Konsumsi'], ['tipe' => 'persediaan']);

        // ── 3. Data Barang & Transaksi Riil ───────────────────────────────────
        
        // A. BUKU PELAUT (1.01.03.01.014.000037)
        $jbBukuPelaut = JenisBarang::updateOrCreate(['nama_generik' => 'BUKU PELAUT (1.01.03.01.014.000037)'], ['kategori_id' => $katATK->id]);
        $pBukuPelaut = Persediaan::updateOrCreate(['jenis_barang_id' => $jbBukuPelaut->id], [
            'merk' => 'Standar Hubla', 'satuan' => 'BUAH', 'stok_minimum' => 200, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBukuPelaut->id, 'no_batch' => 1], [
            'no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Ditjen Hubla', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 350, 'harga_satuan' => 51700, 'sisa_stok' => 0,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBukuPelaut->id, 'no_batch' => 2], [
            'no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Ditjen Hubla', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 1000, 'harga_satuan' => 66156, 'sisa_stok' => 500,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBukuPelaut->id, 'no_batch' => 3], [
            'no_faktur' => 'KSOPBANTEN/08-06', 'supplier' => 'Transfer Masuk Online', 'tanggal_masuk' => '2026-06-08', 'jumlah_masuk' => 500, 'harga_satuan' => 69097, 'sisa_stok' => 500,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBukuPelaut->id, 'no_batch' => 4], [
            'no_faktur' => 'KSOPBANTEN/11-06', 'supplier' => 'Transfer Masuk Online', 'tanggal_masuk' => '2026-06-11', 'jumlah_masuk' => 500, 'harga_satuan' => 69097, 'sisa_stok' => 500,
        ]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pBukuPelaut->id, 'jumlah' => 350, 'tanggal' => '2026-03-09 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Pelayanan Sertifikasi (I.082.151 - I.082.500)', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-03-09',
        ]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pBukuPelaut->id, 'jumlah' => 500, 'tanggal' => '2026-06-30 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Pelayanan Sertifikasi (L.042.001 - L.042.500)', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-06-30',
        ]);

        // B. BBM KUPON 530 (1.01.03.13.001.000003)
        $jbBbmKupon = JenisBarang::updateOrCreate(['nama_generik' => 'BBM KUPON 530 (1.01.03.13.001.000003)'], ['kategori_id' => $katBBM->id]);
        $pBbmKupon = Persediaan::updateOrCreate(['jenis_barang_id' => $jbBbmKupon->id], [
            'merk' => 'Pertamina', 'satuan' => 'LITER', 'stok_minimum' => 500, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBbmKupon->id, 'no_batch' => 1], [
            'no_faktur' => 'BMN/002/2026', 'supplier' => 'Pertamina Banten', 'tanggal_masuk' => '2026-05-30', 'jumlah_masuk' => 600, 'harga_satuan' => 12300, 'sisa_stok' => 100,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBbmKupon->id, 'no_batch' => 2], [
            'no_faktur' => '00002/R', 'supplier' => 'Pertamina Banten', 'tanggal_masuk' => '2026-06-29', 'jumlah_masuk' => 500, 'harga_satuan' => 16250, 'sisa_stok' => 500,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBbmKupon->id, 'no_batch' => 3], [
            'no_faktur' => '00001/R', 'supplier' => 'Pertamina Banten', 'tanggal_masuk' => '2026-06-29', 'jumlah_masuk' => 200, 'harga_satuan' => 16250, 'sisa_stok' => 200,
        ]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pBbmKupon->id, 'jumlah' => 1100, 'tanggal' => '2026-06-15 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Kapal Patroli KNP 530', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-06-15',
        ]);

        // C. AIR MINERAL (1.01.01.08.047.000001)
        $jbAir = JenisBarang::updateOrCreate(['nama_generik' => 'AIR MINERAL (1.01.01.08.047.000001)'], ['kategori_id' => $katLogistik->id]);
        $pAir = Persediaan::updateOrCreate(['jenis_barang_id' => $jbAir->id], [
            'merk' => 'Aqua / Le Minerale', 'satuan' => 'DUS', 'stok_minimum' => 20, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pAir->id, 'no_batch' => 1], ['no_faktur' => '00003/UP_TUP/413670/2026', 'supplier' => 'CV Sumber Segar', 'tanggal_masuk' => '2026-03-05', 'jumlah_masuk' => 16, 'harga_satuan' => 100000, 'sisa_stok' => 16]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pAir->id, 'no_batch' => 2], ['no_faktur' => '00006/UP_TUP/413670/2026', 'supplier' => 'CV Sumber Segar', 'tanggal_masuk' => '2026-03-12', 'jumlah_masuk' => 12, 'harga_satuan' => 100000, 'sisa_stok' => 12]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pAir->id, 'no_batch' => 3], ['no_faktur' => '00010/UP_TUP/413670/2026', 'supplier' => 'CV Sumber Segar', 'tanggal_masuk' => '2026-04-24', 'jumlah_masuk' => 12, 'harga_satuan' => 100000, 'sisa_stok' => 12]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pAir->id, 'no_batch' => 4], ['no_faktur' => '00013/UP_TUP/413670/2026', 'supplier' => 'CV Sumber Segar', 'tanggal_masuk' => '2026-04-24', 'jumlah_masuk' => 24, 'harga_satuan' => 50000, 'sisa_stok' => 24]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pAir->id, 'no_batch' => 5], ['no_faktur' => '00014/UP_TUP/413670/2026', 'supplier' => 'CV Sumber Segar', 'tanggal_masuk' => '2026-05-11', 'jumlah_masuk' => 12, 'harga_satuan' => 100000, 'sisa_stok' => 12]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pAir->id, 'no_batch' => 6], ['no_faktur' => '00017/UP_TUP/413670/2026', 'supplier' => 'CV Sumber Segar', 'tanggal_masuk' => '2026-06-12', 'jumlah_masuk' => 8, 'harga_satuan' => 50000, 'sisa_stok' => 8]);

        // D. KERTAS HVS A4 (1.01.03.02.001.000001)
        $jbHvs = JenisBarang::updateOrCreate(['nama_generik' => 'KERTAS HVS A4 (1.01.03.02.001.000001)'], ['kategori_id' => $katATK->id]);
        $pHvs = Persediaan::updateOrCreate(['jenis_barang_id' => $jbHvs->id], [
            'merk' => 'PaperOne / Sinar Dunia', 'satuan' => 'RIM', 'stok_minimum' => 15, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pHvs->id, 'no_batch' => 1], [
            'no_faktur' => 'KU.105/1/16/KSOP.Btn-26', 'supplier' => 'Toko ATK Merak', 'tanggal_masuk' => '2026-03-05', 'jumlah_masuk' => 100, 'harga_satuan' => 59000, 'sisa_stok' => 10,
        ]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pHvs->id, 'jumlah' => 75, 'tanggal' => '2026-06-01 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Bagian Tata Usaha (Habis Pakai)', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-06-01',
        ]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pHvs->id, 'jumlah' => 10, 'tanggal' => '2026-06-04 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Seksi KBPP', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-06-04',
        ]);

        // E. OLI MESTRANIA 2 T (1.01.03.13.002.000001)
        $jbOli = JenisBarang::updateOrCreate(['nama_generik' => 'Oli Mestrania 2 T (1.01.03.13.002.000001)'], ['kategori_id' => $katBBM->id]);
        $pOli = Persediaan::updateOrCreate(['jenis_barang_id' => $jbOli->id], [
            'merk' => 'Pertamina Mestrania', 'satuan' => 'LITER', 'stok_minimum' => 30, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pOli->id, 'no_batch' => 1], ['no_faktur' => '00012/UP_TUP/413670/2026', 'supplier' => 'PT Pelumas Banten', 'tanggal_masuk' => '2026-04-22', 'jumlah_masuk' => 48, 'harga_satuan' => 58700, 'sisa_stok' => 29]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pOli->id, 'no_batch' => 2], ['no_faktur' => '00019/UP_TUP/413670/2026', 'supplier' => 'PT Pelumas Banten', 'tanggal_masuk' => '2026-06-30', 'jumlah_masuk' => 48, 'harga_satuan' => 65400, 'sisa_stok' => 48]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pOli->id, 'jumlah' => 68, 'tanggal' => '2026-06-15 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Kapal Patroli KNP 530', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-06-15',
        ]);

        // F. KARTU EPAS KECIL (1.01.03.03.999.000001)
        $jbEpas = JenisBarang::updateOrCreate(['nama_generik' => 'KARTU EPAS KECIL (1.01.03.03.999.000001)'], ['kategori_id' => $katATK->id]);
        $pEpas = Persediaan::updateOrCreate(['jenis_barang_id' => $jbEpas->id], [
            'merk' => 'E-Pass KSOP', 'satuan' => 'BUAH', 'stok_minimum' => 500, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pEpas->id, 'no_batch' => 1], ['no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Percetakan Negara', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 2357, 'harga_satuan' => 51000, 'sisa_stok' => 2200]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pEpas->id, 'jumlah' => 157, 'tanggal' => '2026-03-02 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Seksi SHSK', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-03-02',
        ]);

        // G. TINTA EPSON T664 (1.01.03.04.004.000028)
        $jbTinta664 = JenisBarang::updateOrCreate(['nama_generik' => 'Tinta Epson T664 (1.01.03.04.004.000028)'], ['kategori_id' => $katElektronik->id]);
        $pTinta664 = Persediaan::updateOrCreate(['jenis_barang_id' => $jbTinta664->id], [
            'merk' => 'Epson Original', 'satuan' => 'TUBE', 'stok_minimum' => 10, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pTinta664->id, 'no_batch' => 1], ['no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Epson Authorized', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 33, 'harga_satuan' => 250000, 'sisa_stok' => 25]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pTinta664->id, 'jumlah' => 8, 'tanggal' => '2026-06-04 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Bagian TU & KBPP', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-06-04',
        ]);

        // H. BLANKO KWITANSI PUJK (1.01.03.01.014.000019)
        $jbPujk = JenisBarang::updateOrCreate(['nama_generik' => 'BLANKO KWITANSI PUJK (1.01.03.01.014.000019)'], ['kategori_id' => $katATK->id]);
        $pPujk = Persediaan::updateOrCreate(['jenis_barang_id' => $jbPujk->id], [
            'merk' => 'Kemenhub', 'satuan' => 'BLOK', 'stok_minimum' => 20, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pPujk->id, 'no_batch' => 1], ['no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Ditjen Hubla', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 98, 'harga_satuan' => 22330, 'sisa_stok' => 98]);

        // I. BINGKAI FOTO 10 R (1.01.03.05.999.000023)
        $jbBingkai = JenisBarang::updateOrCreate(['nama_generik' => 'BINGKAI FOTO 10 R (1.01.03.05.999.000023)'], ['kategori_id' => $katKebersihan->id]);
        $pBingkai = Persediaan::updateOrCreate(['jenis_barang_id' => $jbBingkai->id], [
            'merk' => 'Standar', 'satuan' => 'PCS', 'stok_minimum' => 10, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pBingkai->id, 'no_batch' => 1], ['no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Toko Mitra', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 100, 'harga_satuan' => 61050, 'sisa_stok' => 100]);

        // J. PITA PRINTER PLQ-20 (1.01.03.04.003.000006)
        $jbPlq = JenisBarang::updateOrCreate(['nama_generik' => 'PITA PRINTER PLQ-20 (1.01.03.04.003.000006)'], ['kategori_id' => $katElektronik->id]);
        $pPlq = Persediaan::updateOrCreate(['jenis_barang_id' => $jbPlq->id], [
            'merk' => 'Epson Passbook', 'satuan' => 'ROLL', 'stok_minimum' => 2, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pPlq->id, 'no_batch' => 1], ['no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Epson Indonesia', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 5, 'harga_satuan' => 643800, 'sisa_stok' => 5]);

        // K. SUKU CADANG KAPAL KNP 372 (GULUNG GENERATOR 35 KVA)
        $jbGen = JenisBarang::updateOrCreate(['nama_generik' => 'GULUNG GENERATOR 35 KVA (1.01.02.01.003.000134)'], ['kategori_id' => $katSparepart->id]);
        $pGen = Persediaan::updateOrCreate(['jenis_barang_id' => $jbGen->id], [
            'merk' => 'Stamford', 'satuan' => 'PCS', 'stok_minimum' => 1, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pGen->id, 'no_batch' => 1], ['no_faktur' => 'KU.105/1/8/KSOP.Btn-26', 'supplier' => 'Bengkel Mesin Bahari', 'tanggal_masuk' => '2026-02-12', 'jumlah_masuk' => 1, 'harga_satuan' => 23754000, 'sisa_stok' => 0]);
        TransaksiPersediaan::updateOrCreate([
            'persediaan_id' => $pGen->id, 'jumlah' => 1, 'tanggal' => '2026-03-02 00:00:00',
        ], [
            'jenis' => 'keluar', 'unit_kerja_penerima' => 'Kapal Patroli KNP 372', 'status' => 'disetujui',
            'diajukan_oleh' => $admin->id, 'diputuskan_oleh' => $admin->id, 'tanggal_keputusan' => '2026-03-02',
        ]);

        // L. PERBEKALAN P3K & KESEHATAN
        $jbP3k = JenisBarang::updateOrCreate(['nama_generik' => 'KASSA STERIL & PERBAN (1.01.04.01.999.000007)'], ['kategori_id' => $katP3K->id]);
        $pP3k = Persediaan::updateOrCreate(['jenis_barang_id' => $jbP3k->id], [
            'merk' => 'Husada', 'satuan' => 'BUAH', 'stok_minimum' => 10, 'ruangan_id' => $gudang->id,
        ]);
        BatchPersediaan::updateOrCreate(['persediaan_id' => $pP3k->id, 'no_batch' => 1], ['no_faktur' => 'SALDO-AWAL-2026', 'supplier' => 'Apotek Kimia Farma Merak', 'tanggal_masuk' => '2026-01-01', 'jumlah_masuk' => 25, 'harga_satuan' => 15000, 'sisa_stok' => 25]);

        // ── 4. Log Aktivitas Riil ─────────────────────────────────────────────
        AuditLog::create([
            'user_id' => $admin->id,
            'user_name' => 'Administrator LOFBI',
            'modul' => 'Persediaan',
            'aksi' => 'Import Real Data',
            'detail' => 'Sinkronisasi Buku Rincian Persediaan BMN Periode 2026 UAKPB KSOP Kelas I Banten (022.04.2900.413670).',
        ]);
    }
}
