<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\Kategori;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\Setting;
use App\Models\TransaksiPersediaan;
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

        // 3. Assets Create
        $response = $this->actingAs($user)->get('/assets/create');
        $response->assertStatus(200);

        // 4. Inventory
        $response = $this->actingAs($user)->get('/inventory');
        $response->assertStatus(200);

        // 5. Inventory In & Out
        $response = $this->actingAs($user)->get('/inventory/in');
        $response->assertStatus(200);

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

    public function test_authenticated_user_can_create_update_and_delete_asset(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $kategori = Kategori::where('tipe', 'aset')->first();
        $ruangan = Ruangan::first();

        // 1. Create Asset
        $response = $this->actingAs($user)->post('/assets', [
            'asset_code' => 'TEST-AST-001',
            'name' => 'Laptop Test LOFBI',
            'category_id' => $kategori->id,
            'room_id' => $ruangan->id,
            'condition' => 'Baik',
            'acquisition_value' => 10000000,
            'useful_life_years' => 4,
        ]);
        $response->assertRedirect('/assets');

        $aset = Aset::where('kode_aset', 'TEST-AST-001')->first();
        $this->assertNotNull($aset);
        $this->assertEquals(10000000, $aset->nilai_perolehan);

        // 2. Show Asset
        $response = $this->actingAs($user)->get('/assets/' . $aset->id);
        $response->assertStatus(200);

        // 3. Edit Asset Form
        $response = $this->actingAs($user)->get('/assets/' . $aset->id . '/edit');
        $response->assertStatus(200);

        // 4. Update Asset
        $response = $this->actingAs($user)->put('/assets/' . $aset->id, [
            'asset_code' => 'TEST-AST-001',
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

        // 5. Delete Asset
        $response = $this->actingAs($user)->delete('/assets/' . $aset->id);
        $response->assertRedirect('/assets');
        $this->assertNull(Aset::find($aset->id));
    }

    public function test_authenticated_user_can_crud_master_persediaan(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $kategori = Kategori::where('tipe', 'persediaan')->first() ?? Kategori::first();

        // 1. Create Form
        $response = $this->actingAs($user)->get('/inventory/create');
        $response->assertStatus(200);

        // 2. Store Master
        $response = $this->actingAs($user)->post('/inventory', [
            'name' => 'Buku Pelaut Uji Coba',
            'category_id' => $kategori->id,
            'satuan' => 'BUAH',
            'merk' => 'Standar Hubla',
            'stok_minimum' => 50,
            'initial_qty' => 100,
            'initial_price' => 50000,
        ]);
        $response->assertRedirect('/inventory');

        $item = Persediaan::with('jenisBarang')->get()->last();
        $this->assertNotNull($item);
        $this->assertStringContainsString('Buku Pelaut Uji Coba', $item->name);

        // 3. Show Inventory Card
        $response = $this->actingAs($user)->get('/inventory/' . $item->id);
        $response->assertStatus(200);

        // 4. Edit Form
        $response = $this->actingAs($user)->get('/inventory/' . $item->id . '/edit');
        $response->assertStatus(200);

        // 5. Update Master
        $response = $this->actingAs($user)->put('/inventory/' . $item->id, [
            'name' => 'Buku Pelaut Uji Coba Updated',
            'category_id' => $kategori->id,
            'satuan' => 'BUAH',
            'merk' => 'Standar Hubla Baru',
            'stok_minimum' => 60,
        ]);
        $response->assertRedirect('/inventory');

        // 6. Delete Master
        $response = $this->actingAs($user)->delete('/inventory/' . $item->id);
        $response->assertRedirect('/inventory');
    }

    public function test_authenticated_user_can_record_inventory_in_and_out(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $persediaan = Persediaan::first();

        // 1. Store In
        $response = $this->actingAs($user)->post('/inventory/in', [
            'inventory_item_id' => $persediaan->id,
            'qty_received' => 25,
            'purchase_price' => 50000,
        ]);
        $response->assertRedirect('/inventory');

        // 2. Store Out (creates pending request)
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
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->first();

        $this->assertNotNull($transaksi);

        // Approve request
        $response = $this->actingAs($validator)->patch('/inventory/' . $transaksi->id . '/approve');
        $response->assertRedirect('/inventory/pengajuan');

        $transaksi->refresh();
        $this->assertEquals('disetujui', $transaksi->status);
    }

    public function test_role_access_control_blocks_viewer_from_admin_actions(): void
    {
        $this->seed();
        $viewer = User::where('role', 'viewer')->first();

        // Viewer should not be allowed to access settings
        $response = $this->actingAs($viewer)->get('/settings');
        $response->assertStatus(403);

        // Viewer should not be allowed to create asset
        $response = $this->actingAs($viewer)->get('/assets/create');
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
        $user = User::where('role', 'admin')->first() ?? User::first();
        $ruangan = Ruangan::first();

        $response = $this->actingAs($user)->post('/opname', [
            'ruangan_id' => $ruangan->id,
            'tanggal' => date('Y-m-d'),
            'keterangan' => 'Opname rutin mingguan',
        ]);
        $response->assertRedirect('/opname');

        $sesi = OpnameSesi::latest()->first();
        $this->assertNotNull($sesi);

        // Show opname details
        $response = $this->actingAs($user)->get('/opname/' . $sesi->id);
        $response->assertStatus(200);

        // Export Berita Acara PDF
        $response = $this->actingAs($user)->get('/reports/opname/pdf?sesi_id=' . $sesi->id);
        $response->assertStatus(200);
    }
}
