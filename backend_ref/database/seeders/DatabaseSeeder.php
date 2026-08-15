<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\MasaManfaatKategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\Setting;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── 5 Role Users sesuai Requirement LOFBI ──────────────────────────────
        $admin = User::updateOrCreate(['email' => 'admin@lofbi.test'], [
            'name' => 'Admin LOFBI',
            'role' => 'admin', // Administrator
            'password' => Hash::make('password'),
        ]);

        $operator = User::updateOrCreate(['email' => 'operator@lofbi.test'], [
            'name' => 'Operator LOFBI',
            'role' => 'operator', // Operator
            'password' => Hash::make('password'),
        ]);

        $validator = User::updateOrCreate(['email' => 'validator@lofbi.test'], [
            'name' => 'Validator LOFBI',
            'role' => 'validator', // Validator
            'password' => Hash::make('password'),
        ]);

        $viewer = User::updateOrCreate(['email' => 'viewer@lofbi.test'], [
            'name' => 'Viewer LOFBI',
            'role' => 'viewer', // Viewer
            'password' => Hash::make('password'),
        ]);

        $pimpinan = User::updateOrCreate(['email' => 'pimpinan@lofbi.test'], [
            'name' => 'Pimpinan KSOP',
            'role' => 'pimpinan', // Pimpinan
            'password' => Hash::make('password'),
        ]);

        // ── Ruangan ───────────────────────────────────────────────────────
        $ruangTU = Ruangan::updateOrCreate(['nama' => 'Ruang Tata Usaha'], ['gedung' => 'Gedung Utama']);
        $gudang = Ruangan::updateOrCreate(['nama' => 'Gudang Persediaan'], ['gedung' => 'Gedung Utama']);
        $ruangKepala = Ruangan::updateOrCreate(['nama' => 'Ruang Kepala'], ['gedung' => 'Gedung Utama']);

        // ── Kategori & Masa Manfaat ────────────────────────────────────────
        $atk = Kategori::updateOrCreate(['nama' => 'ATK'], ['tipe' => 'persediaan']);
        $rtg = Kategori::updateOrCreate(['nama' => 'Rumah Tangga'], ['tipe' => 'persediaan']);
        $elektronik = Kategori::updateOrCreate(['nama' => 'Elektronik'], ['tipe' => 'aset']);
        $furnitur = Kategori::updateOrCreate(['nama' => 'Furnitur'], ['tipe' => 'aset']);

        MasaManfaatKategori::updateOrCreate(['kategori_id' => $elektronik->id], ['masa_manfaat_tahun' => 4]);
        MasaManfaatKategori::updateOrCreate(['kategori_id' => $furnitur->id], ['masa_manfaat_tahun' => 8]);

        // ── Jenis Barang ───────────────────────────────────────────────────
        $laptop = JenisBarang::updateOrCreate(['nama_generik' => 'Laptop'], ['kategori_id' => $elektronik->id]);
        $printer = JenisBarang::updateOrCreate(['nama_generik' => 'Printer'], ['kategori_id' => $elektronik->id]);
        $meja = JenisBarang::updateOrCreate(['nama_generik' => 'Meja Kerja'], ['kategori_id' => $furnitur->id]);
        $pulpen = JenisBarang::updateOrCreate(['nama_generik' => 'Pulpen'], ['kategori_id' => $atk->id]);
        $kertas = JenisBarang::updateOrCreate(['nama_generik' => 'Kertas A4'], ['kategori_id' => $atk->id]);
        $tinta = JenisBarang::updateOrCreate(['nama_generik' => 'Tinta Printer'], ['kategori_id' => $rtg->id]);

        // ── Aset Sample dengan Field Baru ─────────────────────────────────
        Aset::updateOrCreate(['kode_aset' => 'ELK-LAP-001'], [
            'jenis_barang_id' => $laptop->id,
            'sub_kategori' => 'Elektronik',
            'merk' => 'Lenovo',
            'model' => 'ThinkPad E14',
            'kondisi' => 'baik',
            'ruangan_id' => $ruangTU->id,
            'nilai_perolehan' => 12_000_000,
            'tanggal_perolehan' => '2023-01-15',
            'masa_manfaat' => 4,
            'metode_penyusutan' => 'Garis Lurus',
            'akumulasi_penyusutan' => 3_000_000,
            'nilai_buku' => 9_000_000,
            'last_opname_date' => '2026-08-05',
        ]);

        Aset::updateOrCreate(['kode_aset' => 'ELK-LAP-002'], [
            'jenis_barang_id' => $laptop->id,
            'sub_kategori' => 'Elektronik',
            'merk' => 'ASUS',
            'model' => 'ExpertBook B1',
            'kondisi' => 'rusak_ringan',
            'ruangan_id' => $ruangKepala->id,
            'nilai_perolehan' => 10_500_000,
            'tanggal_perolehan' => '2022-07-01',
            'masa_manfaat' => 4,
            'metode_penyusutan' => 'Saldo Menurun',
            'akumulasi_penyusutan' => 5_250_000,
            'nilai_buku' => 5_250_000,
            'last_opname_date' => '2026-06-28',
        ]);

        Aset::updateOrCreate(['kode_aset' => 'ELK-PRN-001'], [
            'jenis_barang_id' => $printer->id,
            'sub_kategori' => 'Elektronik',
            'merk' => 'Canon',
            'model' => 'PIXMA G2020',
            'kondisi' => 'baik',
            'ruangan_id' => $ruangTU->id,
            'nilai_perolehan' => 2_500_000,
            'tanggal_perolehan' => '2024-03-10',
            'masa_manfaat' => 5,
            'metode_penyusutan' => 'Garis Lurus',
            'akumulasi_penyusutan' => 312_500,
            'nilai_buku' => 2_187_500,
            'last_opname_date' => '2026-08-05',
        ]);

        Aset::updateOrCreate(['kode_aset' => 'FUR-MJK-001'], [
            'jenis_barang_id' => $meja->id,
            'sub_kategori' => 'Furnitur',
            'merk' => 'Olympic',
            'model' => null,
            'kondisi' => 'baik',
            'ruangan_id' => $ruangTU->id,
            'nilai_perolehan' => 1_800_000,
            'tanggal_perolehan' => '2021-01-01',
            'masa_manfaat' => 8,
            'metode_penyusutan' => 'Garis Lurus',
            'akumulasi_penyusutan' => 675_000,
            'nilai_buku' => 1_125_000,
            'last_opname_date' => '2026-08-05',
        ]);

        Aset::updateOrCreate(['kode_aset' => 'FUR-MJK-002'], [
            'jenis_barang_id' => $meja->id,
            'sub_kategori' => 'Furnitur',
            'merk' => 'Olympic',
            'model' => null,
            'kondisi' => 'rusak_berat',
            'ruangan_id' => $ruangKepala->id,
            'nilai_perolehan' => 1_800_000,
            'tanggal_perolehan' => '2019-01-01',
            'masa_manfaat' => 8,
            'metode_penyusutan' => 'Saldo Menurun',
            'akumulasi_penyusutan' => 1_800_000,
            'nilai_buku' => 0,
            'last_opname_date' => '2026-08-03',
        ]);

        // ── Persediaan & Batch FIFO ─────────────────────────────────────────────
        $persediaanPulpen = Persediaan::updateOrCreate(
            ['jenis_barang_id' => $pulpen->id, 'merk' => null],
            ['satuan' => 'pcs', 'stok_minimum' => 10, 'ruangan_id' => $gudang->id]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanPulpen->id, 'no_batch' => 1],
            [
                'no_referensi' => 'REF-2026-001',
                'no_faktur' => 'INV-2026-012',
                'nota_dinas' => 'ND/2026/01',
                'supplier' => 'Sinar Dunia',
                'tanggal_masuk' => '2026-05-01',
                'jumlah_masuk' => 50,
                'harga_satuan' => 2_500,
                'sisa_stok' => 12,
            ]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanPulpen->id, 'no_batch' => 2],
            [
                'no_referensi' => 'REF-2026-008',
                'no_faktur' => 'INV-2026-045',
                'nota_dinas' => 'ND/2026/04',
                'supplier' => 'Fajar Abadi',
                'tanggal_masuk' => '2026-06-15',
                'jumlah_masuk' => 40,
                'harga_satuan' => 2_600,
                'sisa_stok' => 38,
            ]
        );

        $persediaanKertas = Persediaan::updateOrCreate(
            ['jenis_barang_id' => $kertas->id, 'merk' => 'Sinar Dunia'],
            ['satuan' => 'rim', 'stok_minimum' => 5, 'ruangan_id' => $gudang->id]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanKertas->id, 'no_batch' => 1],
            [
                'no_referensi' => 'REF-2026-011',
                'no_faktur' => 'INV-2026-089',
                'nota_dinas' => 'ND/2026/08',
                'supplier' => 'Kertas Nusantara',
                'tanggal_masuk' => '2026-04-10',
                'jumlah_masuk' => 20,
                'harga_satuan' => 48_000,
                'sisa_stok' => 8,
            ]
        );

        $persediaanTinta = Persediaan::updateOrCreate(
            ['jenis_barang_id' => $tinta->id, 'merk' => 'Canon'],
            ['satuan' => 'botol', 'stok_minimum' => 2, 'ruangan_id' => $gudang->id]
        );
        BatchPersediaan::updateOrCreate(
            ['persediaan_id' => $persediaanTinta->id, 'no_batch' => 1],
            [
                'no_referensi' => 'REF-2026-019',
                'no_faktur' => 'INV-2026-102',
                'nota_dinas' => 'ND/2026/11',
                'supplier' => 'Canon Authorized',
                'tanggal_masuk' => '2026-08-01',
                'jumlah_masuk' => 3,
                'harga_satuan' => 90_000,
                'sisa_stok' => 1,
            ]
        );

        // ── Transaksi Pengajuan ─────────────────────────────────────────────
        TransaksiPersediaan::updateOrCreate(
            [
                'persediaan_id' => $persediaanPulpen->id,
                'jenis' => 'keluar',
                'status' => 'menunggu',
            ],
            [
                'jumlah' => 15,
                'tanggal' => now()->toDateString(),
                'unit_kerja_penerima' => 'Seksi Kepegawaian',
                'diajukan_oleh' => $admin->id,
            ]
        );

        // ── Initial Audit Logs ──────────────────────────────────────────────
        AuditLog::create([
            'user_id' => $validator->id,
            'user_name' => 'Validator LOFBI',
            'modul' => 'Aset',
            'aksi' => 'Edit',
            'detail' => 'Perbarui kondisi ELK-PRN-001 menjadi baik',
        ]);
        AuditLog::create([
            'user_id' => $operator->id,
            'user_name' => 'Operator LOFBI',
            'modul' => 'Persediaan',
            'aksi' => 'Tambah',
            'detail' => 'Stok Pulpen +50 pcs, supplier Sinar Dunia',
        ]);
        AuditLog::create([
            'user_id' => $admin->id,
            'user_name' => 'Admin LOFBI',
            'modul' => 'Opname',
            'aksi' => 'Edit',
            'detail' => 'Update hasil opname OPN-001 Ruang Tata Usaha',
        ]);
        AuditLog::create([
            'user_id' => $pimpinan->id,
            'user_name' => 'Pimpinan KSOP',
            'modul' => 'Approval',
            'aksi' => 'Approve',
            'detail' => 'Pengajuan barang keluar Seksi Kepegawaian',
        ]);

        // ── Default System Settings ─────────────────────────────────────────
        Setting::updateOrCreate(['key' => 'nama_ksop'], ['value' => 'KSOP Kelas I Banten']);
        Setting::updateOrCreate(['key' => 'alamat_instansi'], ['value' => 'Jl. Yos Sudarso No. 1, Bandar Lampung']);
        Setting::updateOrCreate(['key' => 'logo_url'], ['value' => '/public/images/logo-ksop.png']);
        Setting::updateOrCreate(['key' => 'format_tanggal'], ['value' => 'DD MMM YYYY']);
        Setting::updateOrCreate(['key' => 'tahun_anggaran'], ['value' => '2026']);
    }
}
