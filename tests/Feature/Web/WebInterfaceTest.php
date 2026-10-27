<?php

namespace Tests\Feature\Web;

use App\Models\Aset;
use App\Models\BatchPersediaan;
use App\Models\Kategori;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\Setting;
use App\Models\TransaksiPersediaan;
use App\Models\UploadBaris;
use App\Models\UploadPaket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_all_web_pages(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();

        // 1. Dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // 2. Assets
        $response = $this->actingAs($user)->get('/assets');
        $response->assertStatus(200);

        // 3. Upload & Validasi Staging
        $response = $this->actingAs($user)->get('/upload');
        $response->assertStatus(200);

        // 4. Inventory
        $response = $this->actingAs($user)->get('/inventory');
        $response->assertStatus(200);

        // 5. Inventory Out (Pengajuan)
        $response = $this->actingAs($user)->get('/inventory/out');
        $response->assertStatus(200);

        // 6. Opname
        $response = $this->actingAs($user)->get('/opname');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/opname/create');
        $response->assertStatus(200);

        // 7. Reports
        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);

        // 8. Profile & Notifications & Settings
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_view_update_and_delete_asset(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $kategori = Kategori::where('tipe', 'aset')->first() ?? Kategori::first();
        $ruangan = Ruangan::first();
        $aset = Aset::first();

        // 1. Show Asset
        $response = $this->actingAs($user)->get('/assets/'.$aset->id);
        $response->assertStatus(200);

        // 2. Edit Asset Form
        $response = $this->actingAs($user)->get('/assets/'.$aset->id.'/edit');
        $response->assertStatus(200);

        // 3. Update Asset
        $response = $this->actingAs($user)->put('/assets/'.$aset->id, [
            'asset_code' => $aset->kode_aset,
            'name' => 'Laptop Test LOFBI Updated',
            'category_id' => $kategori->id,
            'room_id' => $ruangan->id,
            'condition' => 'Rusak Ringan',
            'acquisition_value' => 5000000,
            'useful_life_years' => 5,
        ]);
        $response->assertRedirect('/assets');

        $aset->refresh();
        $this->assertEquals('rusak_ringan', $aset->kondisi);

        // 4. Delete Asset
        $response = $this->actingAs($user)->delete('/assets/'.$aset->id);
        $response->assertRedirect('/assets');
        $this->assertNull(Aset::find($aset->id));
    }

    public function test_authenticated_user_can_view_update_and_delete_persediaan(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $kategori = Kategori::where('tipe', 'persediaan')->first() ?? Kategori::first();
        $item = Persediaan::first();

        // 1. Show Inventory Card
        $response = $this->actingAs($user)->get('/inventory/'.$item->id);
        $response->assertStatus(200);

        // 2. Edit Form
        $response = $this->actingAs($user)->get('/inventory/'.$item->id.'/edit');
        $response->assertStatus(200);

        // 3. Update Master
        $response = $this->actingAs($user)->put('/inventory/'.$item->id, [
            'name' => 'Buku Pelaut Uji Coba Updated',
            'category_id' => $kategori->id,
            'satuan' => 'BUAH',
            'merk' => 'Standar Hubla Baru',
            'stok_minimum' => 60,
        ]);
        $response->assertRedirect('/inventory');

        // 4. Delete Master
        $response = $this->actingAs($user)->delete('/inventory/'.$item->id);
        $response->assertRedirect('/inventory');
    }

    public function test_authenticated_user_can_record_inventory_out(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $persediaan = Persediaan::first();

        // Store Out (creates pending request)
        $response = $this->actingAs($user)->post('/inventory/out', [
            'inventory_item_id' => $persediaan->id,
            'qty_out' => 5,
            'unit_kerja_penerima' => 'Seksi Operasional',
        ]);
        $response->assertRedirect('/inventory');

        $pending = TransaksiPersediaan::where('persediaan_id', $persediaan->id)
            ->where('status', 'menunggu')
            ->first();
        $this->assertNotNull($pending);
    }

    public function test_validator_can_approve_and_reject_inventory_requests(): void
    {
        $this->seed();
        $validator = User::where('role', 'validator')->first();
        $admin = User::where('role', 'admin')->first();
        $operator = User::where('role', 'operator')->first();
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->first();

        $this->assertNotNull($transaksi);

        // Admin mendapat 403 saat mencoba menyetujui atau menolak (hanya validator)
        $response = $this->actingAs($admin)->patch('/inventory/'.$transaksi->id.'/approve');
        $response->assertStatus(403);
        $response = $this->actingAs($admin)->patch('/inventory/'.$transaksi->id.'/reject', ['alasan' => 'Ditolak admin']);
        $response->assertStatus(403);
        $transaksi->refresh();
        $this->assertSame('menunggu', $transaksi->status);

        // Operator mendapat 403 saat mencoba menyetujui atau menolak
        $response = $this->actingAs($operator)->patch('/inventory/'.$transaksi->id.'/approve');
        $response->assertStatus(403);
        $response = $this->actingAs($operator)->patch('/inventory/'.$transaksi->id.'/reject', ['alasan' => 'Ditolak operator']);
        $response->assertStatus(403);

        // Validator berhasil menyetujui
        $response = $this->actingAs($validator)->patch('/inventory/'.$transaksi->id.'/approve');
        $response->assertRedirect('/inventory/pengajuan');

        $transaksi->refresh();
        $this->assertSame('disetujui', $transaksi->status);
    }

    public function test_admin_and_operator_cannot_approve_or_reject_opname(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $operator = User::where('role', 'operator')->first();
        $validator = User::where('role', 'validator')->first();

        // Buat sesi opname dengan status menunggu_persetujuan
        $response = $this->actingAs($admin)->post('/opname', [
            'periode' => 'Semester I 2026',
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Opname untuk test role',
        ]);
        $response->assertRedirect('/opname');

        $sesi = OpnameSesi::latest()->first();

        // Submit hasil fisik agar jadi menunggu_persetujuan
        $stokFisikData = [];
        foreach ($sesi->details as $detail) {
            $stokFisikData[$detail->id] = $detail->stok_buku;
        }
        $this->actingAs($admin)->post('/opname/'.$sesi->id.'/save-fisik', [
            'stok_fisik' => $stokFisikData,
        ]);
        $sesi->refresh();
        $this->assertSame('menunggu_persetujuan', $sesi->status);

        // Admin mendapat 403 saat mencoba menyetujui atau menolak opname
        $response = $this->actingAs($admin)->post('/opname/'.$sesi->id.'/approve');
        $response->assertStatus(403);
        $response = $this->actingAs($admin)->post('/opname/'.$sesi->id.'/reject', ['catatan_penolakan' => 'Ditolak admin']);
        $response->assertStatus(403);
        $sesi->refresh();
        $this->assertSame('menunggu_persetujuan', $sesi->status);

        // Operator mendapat 403 saat mencoba menyetujui atau menolak opname
        $response = $this->actingAs($operator)->post('/opname/'.$sesi->id.'/approve');
        $response->assertStatus(403);
        $response = $this->actingAs($operator)->post('/opname/'.$sesi->id.'/reject', ['catatan_penolakan' => 'Ditolak operator']);
        $response->assertStatus(403);

        // Validator berhasil menyetujui opname
        $response = $this->actingAs($validator)->post('/opname/'.$sesi->id.'/approve');
        $response->assertRedirect('/opname/'.$sesi->id);
        $sesi->refresh();
        $this->assertSame('disetujui', $sesi->status);
    }

    public function test_pengajuan_view_renders_action_buttons_only_for_validator(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $validator = User::where('role', 'validator')->first();
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->first();

        $this->assertNotNull($transaksi);

        // Admin melihat halaman pengajuan tetapi TIDAK ada tombol Setujui/Tolak
        $response = $this->actingAs($admin)->get('/inventory/pengajuan');
        $response->assertStatus(200);
        $response->assertDontSee('inventory.approve', false);
        $response->assertSee('Menunggu validator');

        // Validator melihat halaman pengajuan dengan tombol Setujui dan Tolak
        $response = $this->actingAs($validator)->get('/inventory/pengajuan');
        $response->assertStatus(200);
        $response->assertSee('Setujui');
        $response->assertSee('Tolak');
        $response->assertDontSee('Menunggu validator');
    }

    public function test_opname_view_renders_action_buttons_only_for_validator(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $operator = User::where('role', 'operator')->first();
        $validator = User::where('role', 'validator')->first();

        // Buat sesi opname status menunggu_persetujuan
        $this->actingAs($admin)->post('/opname', [
            'periode' => 'Semester I 2026',
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Opname view test',
        ]);
        $sesi = OpnameSesi::latest()->first();
        $stokFisikData = [];
        foreach ($sesi->details as $detail) {
            $stokFisikData[$detail->id] = $detail->stok_buku;
        }
        $this->actingAs($admin)->post('/opname/'.$sesi->id.'/save-fisik', ['stok_fisik' => $stokFisikData]);
        $sesi->refresh();

        // Admin dan Operator TIDAK melihat tombol Setujui Opname maupun modal Tolak
        $responseAdmin = $this->actingAs($admin)->get('/opname/'.$sesi->id);
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertDontSee('Setujui Opname');
        $responseAdmin->assertDontSee('modalRejectOpname');

        $responseOperator = $this->actingAs($operator)->get('/opname/'.$sesi->id);
        $responseOperator->assertStatus(200);
        $responseOperator->assertDontSee('Setujui Opname');
        $responseOperator->assertDontSee('modalRejectOpname');

        // Validator melihat tombol Setujui Opname dan modal Tolak
        $responseValidator = $this->actingAs($validator)->get('/opname/'.$sesi->id);
        $responseValidator->assertStatus(200);
        $responseValidator->assertSee('Setujui Opname');
        $responseValidator->assertSee('modalRejectOpname');
    }

    public function test_operator_can_access_dashboard_assets_inventory_opname_reports_and_create_pengajuan(): void
    {
        $this->seed();
        $operator = User::where('role', 'operator')->first() ?? User::factory()->create(['role' => 'operator']);

        // (a) Dashboard
        $this->actingAs($operator)->get('/dashboard')->assertStatus(200);

        // (b) Aset - lihat daftar & detail
        $this->actingAs($operator)->get('/assets')->assertStatus(200);
        $aset = Aset::first();
        $this->actingAs($operator)->get('/assets/'.$aset->id)->assertStatus(200);

        // (c) Persediaan - lihat daftar & detail
        $this->actingAs($operator)->get('/inventory')->assertStatus(200);
        $item = Persediaan::first();
        $this->actingAs($operator)->get('/inventory/'.$item->id)->assertStatus(200);

        // (d) Form ajukan barang keluar
        $this->actingAs($operator)->get('/inventory/out')->assertStatus(200);

        // (e) Opname - lihat
        $this->actingAs($operator)->get('/opname')->assertStatus(200);
        $this->actingAs($operator)->get('/opname/create')->assertStatus(200);

        // (f) Laporan
        $this->actingAs($operator)->get('/reports')->assertStatus(200);

        // (g) Upload & Validasi
        $this->actingAs($operator)->get('/upload')->assertStatus(200);

        // (h) Operator DILARANG akses settings
        $this->actingAs($operator)->get('/settings')->assertStatus(403);

        // (i) Operator DILARANG melihat daftar pengajuan (hanya admin & validator)
        $this->actingAs($operator)->get('/inventory/pengajuan')->assertStatus(403);
    }

    public function test_validator_can_access_dashboard_and_reports(): void
    {
        $this->seed();
        $validator = User::where('role', 'validator')->first() ?? User::factory()->create(['role' => 'validator']);

        // (b) Dashboard
        $this->actingAs($validator)->get('/dashboard')->assertStatus(200);

        // Aset & persediaan - lihat
        $this->actingAs($validator)->get('/assets')->assertStatus(200);
        $this->actingAs($validator)->get('/inventory')->assertStatus(200);

        // Opname - lihat
        $this->actingAs($validator)->get('/opname')->assertStatus(200);

        // Laporan
        $this->actingAs($validator)->get('/reports')->assertStatus(200);

        // Upload - lihat
        $this->actingAs($validator)->get('/upload')->assertStatus(200);

        // Pengajuan - lihat
        $this->actingAs($validator)->get('/inventory/pengajuan')->assertStatus(200);

        // Validator DILARANG: settings, ajukan barang keluar (POST), buat sesi opname (POST)
        $this->actingAs($validator)->get('/settings')->assertStatus(403);
        $this->actingAs($validator)->get('/inventory/out')->assertStatus(403);
    }

    public function test_pimpinan_can_access_dashboard_assets_inventory_reports_and_notifications(): void
    {
        $this->seed();
        $pimpinan = User::where('role', 'pimpinan')->first() ?? User::factory()->create(['role' => 'pimpinan']);
        $aset = Aset::first();
        $item = Persediaan::first();

        // (c) Pimpinan 200 pada: dashboard, daftar aset, daftar persediaan, laporan, notifikasi
        $this->actingAs($pimpinan)->get('/dashboard')->assertStatus(200);
        $this->actingAs($pimpinan)->get('/assets')->assertStatus(200);
        $this->actingAs($pimpinan)->get('/assets/'.$aset->id)->assertStatus(200);
        $this->actingAs($pimpinan)->get('/inventory')->assertStatus(200);
        $this->actingAs($pimpinan)->get('/inventory/'.$item->id)->assertStatus(200);
        $this->actingAs($pimpinan)->get('/reports')->assertStatus(200);
        $this->actingAs($pimpinan)->get('/notifications')->assertStatus(200);
        $this->actingAs($pimpinan)->get('/profile')->assertStatus(200);

        // (c) Pimpinan 403 pada: /upload, /opname, pengajuan barang keluar, settings
        $this->actingAs($pimpinan)->get('/upload')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/opname')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/inventory/out')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/inventory/pengajuan')->assertStatus(403);
        $this->actingAs($pimpinan)->get('/settings')->assertStatus(403);
    }

    public function test_only_admin_can_access_settings_and_backup(): void
    {
        // (d) Hanya admin pada /settings dan backup
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $operator = User::where('role', 'operator')->first();
        $validator = User::where('role', 'validator')->first();
        $pimpinan = User::where('role', 'pimpinan')->first();

        $this->actingAs($admin)->get('/settings')->assertStatus(200);
        $this->actingAs($admin)->get('/settings/backup')->assertStatus(200);

        foreach ([$operator, $validator, $pimpinan] as $user) {
            $this->actingAs($user)->get('/settings')->assertStatus(403);
            $this->actingAs($user)->get('/settings/backup')->assertStatus(403);
        }
    }

    public function test_role_access_control_blocks_pimpinan_from_admin_actions(): void
    {
        $this->seed();
        $pimpinan = User::where('role', 'pimpinan')->first() ?? User::factory()->create(['role' => 'pimpinan']);
        $aset = Aset::first();

        // Pimpinan dilarang mengakses pengaturan sistem (hanya admin)
        $response = $this->actingAs($pimpinan)->get('/settings');
        $response->assertStatus(403);

        // Pimpinan dilarang mengakses form edit aset (hanya admin & operator)
        $response = $this->actingAs($pimpinan)->get('/assets/'.$aset->id.'/edit');
        $response->assertStatus(403);
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $this->seed();
        $user = User::first();

        // Update profile
        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Baru LOFBI',
            'email' => 'namabaru@lofbi.test',
        ]);
        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('Nama Baru LOFBI', $user->name);

        // Update password
        $response = $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);
        $response->assertRedirect();
    }

    public function test_admin_can_save_settings_and_download_backup(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();

        // Save settings
        $response = $this->actingAs($admin)->post('/settings', [
            'nama_aplikasi' => 'LOFBI KSOP Banten 2026',
            'nama_ksop' => 'KSOP Kelas I Banten Utama',
            'alamat_instansi' => 'Jl. Pelabuhan Banten No. 99',
            'peringatan_stok' => '1',
            'notif_opname' => '1',
            'laporan_harian' => '0',
        ]);
        $response->assertRedirect('/settings');

        $this->assertEquals('LOFBI KSOP Banten 2026', Setting::where('key', 'nama_aplikasi')->value('value'));

        // Download backup
        $response = $this->actingAs($admin)->get('/settings/backup');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_opname_session(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $validator = User::where('role', 'validator')->first();

        // 1. Buka sesi opname baru (status: draft)
        $response = $this->actingAs($admin)->post('/opname', [
            'periode' => 'Semester I 2026',
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Opname Fisik Persediaan Semester I TA 2026',
        ]);
        $response->assertRedirect('/opname');

        $sesi = OpnameSesi::latest()->first();
        $this->assertNotNull($sesi);
        $this->assertSame('draft', $sesi->status);
        $this->assertSame('Semester I 2026', $sesi->periode);
        $this->assertGreaterThan(0, $sesi->details->count());

        // 2. Akses halaman Input Fisik
        $response = $this->actingAs($admin)->get('/opname/'.$sesi->id.'/input-fisik');
        $response->assertStatus(200);

        // 3. Simpan Hasil Hitung Fisik (status: menunggu_persetujuan)
        $stokFisikData = [];
        foreach ($sesi->details as $detail) {
            // Kita beri selisih -1 untuk item pertama untuk menguji selisih
            $stokFisikData[$detail->id] = max(0, $detail->stok_buku - 1);
        }

        $response = $this->actingAs($admin)->post('/opname/'.$sesi->id.'/save-fisik', [
            'stok_fisik' => $stokFisikData,
        ]);
        $response->assertRedirect('/opname/'.$sesi->id);

        $sesi->refresh();
        $this->assertSame('menunggu_persetujuan', $sesi->status);

        // 4. Validator Menyetujui Opname (status: disetujui)
        $response = $this->actingAs($validator)->post('/opname/'.$sesi->id.'/approve');
        $response->assertRedirect('/opname/'.$sesi->id);

        $sesi->refresh();
        $this->assertSame('disetujui', $sesi->status);
        $this->assertSame($validator->id, $sesi->approver_id);

        // 5. Show opname details
        $response = $this->actingAs($admin)->get('/opname/'.$sesi->id);
        $response->assertStatus(200);

        // 6. Export Berita Acara PDF
        $response = $this->actingAs($admin)->get('/reports/opname/pdf?sesi_id='.$sesi->id);
        $response->assertStatus(200);
    }

    public function test_operator_can_also_create_opname_session_and_input_fisik(): void
    {
        $this->seed();
        $operator = User::where('role', 'operator')->first() ?? User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($operator)->post('/opname', [
            'periode' => 'Semester II 2026',
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Opname oleh Operator',
        ]);
        $response->assertRedirect('/opname');

        $sesi = OpnameSesi::latest()->first();
        $this->assertNotNull($sesi);
        $this->assertEquals('draft', $sesi->status);

        $response = $this->actingAs($operator)->get('/opname/'.$sesi->id.'/input-fisik');
        $response->assertStatus(200);
    }

    public function test_dashboard_mutation_chart_renders_real_database_data_for_last_six_months(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $persediaan = Persediaan::first() ?? Persediaan::create([
            'jenis_barang_id' => 1,
            'satuan' => 'buah',
            'stok_minimum' => 5,
        ]);

        // Bersihkan transaksi persediaan agar data chart terkontrol penuh dalam tes
        TransaksiPersediaan::query()->delete();
        BatchPersediaan::whereNotNull('upload_baris_id')->delete();

        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $expectedLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $expectedLabels[] = $namaBulan[(int) $month->format('n')];
        }

        // 1. Transaksi bulan berjalan: masuk = 45, keluar disetujui = 20, keluar menunggu = 99 (tidak boleh dihitung)
        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'masuk',
            'jumlah' => 45,
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'disetujui',
        ]);
        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => 20,
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'disetujui',
        ]);
        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => 99,
            'tanggal' => now()->format('Y-m-d'),
            'status' => 'menunggu',
        ]);

        // 2. Transaksi 2 bulan lalu: masuk = 30, keluar disetujui = 10
        $twoMonthsAgo = now()->startOfMonth()->subMonths(2)->addDays(5);
        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'masuk',
            'jumlah' => 30,
            'tanggal' => $twoMonthsAgo->format('Y-m-d'),
            'status' => 'disetujui',
        ]);
        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => 10,
            'tanggal' => $twoMonthsAgo->format('Y-m-d'),
            'status' => 'disetujui',
        ]);

        // 3. Transaksi 7 bulan lalu (di luar jendela 6 bulan): tidak boleh masuk hitungan
        $sevenMonthsAgo = now()->startOfMonth()->subMonths(7);
        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'masuk',
            'jumlah' => 500,
            'tanggal' => $sevenMonthsAgo->format('Y-m-d'),
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);

        // Pastikan variabel view berisi data dinamis
        $response->assertViewHas('chartLabels', $expectedLabels);
        $chartMasuk = $response->viewData('chartMasuk');
        $chartKeluar = $response->viewData('chartKeluar');

        $this->assertCount(6, $chartMasuk);
        $this->assertCount(6, $chartKeluar);

        // Index 5 adalah bulan berjalan
        $this->assertSame(45, $chartMasuk[5]);
        $this->assertSame(20, $chartKeluar[5]);

        // Index 3 adalah 2 bulan lalu
        $this->assertSame(30, $chartMasuk[3]);
        $this->assertSame(10, $chartKeluar[3]);

        // Index 4 adalah 1 bulan lalu (tanpa transaksi = 0)
        $this->assertSame(0, $chartMasuk[4]);
        $this->assertSame(0, $chartKeluar[4]);

        // Pastikan view HTML tidak lagi memuat fallback statis lama
        $response->assertDontSee('[10, 25, 50, 40, 30, 93]', false);
        $response->assertDontSee('[5, 12, 20, 15, 25, 0]', false);
    }

    public function test_logout_requires_post_and_get_is_disallowed(): void
    {
        $this->seed();
        $user = User::first() ?? User::factory()->create();

        // GET /logout tidak diizinkan (405 Method Not Allowed)
        $getResponse = $this->actingAs($user)->get('/logout');
        $getResponse->assertStatus(405);

        // POST /logout berhasil mengeluarkan pengguna dan me-redirect ke login
        $postResponse = $this->actingAs($user)->post('/logout');
        $postResponse->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_dashboard_mutation_chart_includes_approved_upload_batches_in_current_month(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $validator = User::where('role', 'validator')->first() ?? User::factory()->create(['role' => 'validator']);
        $operator = User::where('role', 'operator')->first() ?? User::factory()->create(['role' => 'operator']);

        // Bersihkan transaksi dan batch upload
        TransaksiPersediaan::query()->delete();
        BatchPersediaan::whereNotNull('upload_baris_id')->delete();

        // 1. Buat paket dan baris unggahan
        $paket = UploadPaket::create([
            'nama_file' => 'bahan_opname_chart_test.xlsx',
            'path_file' => 'uploads/bahan_opname_chart_test.xlsx',
            'jenis' => 'sakti',
            'diunggah_oleh' => $operator->id,
            'jumlah_baris' => 1,
            'status' => 'menunggu',
        ]);

        $baris = UploadBaris::create([
            'upload_paket_id' => $paket->id,
            'nomor_baris' => 1,
            'kode_barang_lengkap' => '1010301014000088',
            'nama_barang' => 'Barang Masuk Lewat Unggahan',
            'satuan' => 'BUAH',
            'saldo_sistem' => 75,
            'status' => 'menunggu',
        ]);

        // Sebelum disetujui, chart bulan berjalan = 0
        $resBefore = $this->actingAs($admin)->get('/dashboard');
        $chartMasukBefore = $resBefore->viewData('chartMasuk');
        $this->assertSame(0, $chartMasukBefore[5]);

        // 2. Validator menyetujui baris unggahan
        $this->actingAs($validator)->post("/upload/baris/{$baris->id}/setujui");

        // 3. Setelah disetujui, grafik bulan berjalan memuat jumlah batch tersebut (75)
        $resAfter = $this->actingAs($admin)->get('/dashboard');
        $chartMasukAfter = $resAfter->viewData('chartMasuk');
        $this->assertSame(75, $chartMasukAfter[5]);
    }

    public function test_pages_do_not_contain_double_escaped_ampersand(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        foreach (['/notifications', '/upload', '/reports'] as $uri) {
            $response = $this->actingAs($admin)->get($uri);
            $response->assertStatus(200);
            $response->assertDontSee('&amp;amp;', false);
        }
    }

    public function test_inventory_index_renders_datatables_and_hidden_sakti_search_code(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $item = Persediaan::first();
        BatchPersediaan::create([
            'persediaan_id' => $item->id,
            'no_batch' => 999,
            'no_faktur' => 'SAKTI-TEST-999',
            'tanggal_masuk' => now()->toDateString(),
            'jumlah_masuk' => 10,
            'sisa_stok' => 10,
            'harga_satuan' => 15000,
            'harga_belum_diisi' => false,
            'kode_barang_lengkap' => '1.01.01.08.047.000001',
        ]);

        $response = $this->actingAs($admin)->get('/inventory');

        $response->assertStatus(200);
        $response->assertSee('id="tabelPersediaan"', false);
        $response->assertSee('Cari nama, kode, atau kategori');
        $response->assertSee('1010108047000001');
        $response->assertSee('visually-hidden');
    }
}
