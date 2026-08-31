<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_access_all_web_pages(): void
    {
        $this->seed();
        $user = User::first();

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

        // 7. Reports & Download
        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);

        // 8. Profile & Settings
        $response = $this->actingAs($user)->get('/profile');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_update_and_delete_asset(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $kategori = Kategori::first();
        $ruangan = Ruangan::first();

        // 1. Create Asset
        $response = $this->actingAs($user)->post('/assets', [
            'asset_code' => 'TEST-AST-001',
            'name' => 'Printer HP LaserJet Pro',
            'category_id' => $kategori->id,
            'room_id' => $ruangan->id,
            'condition' => 'Baik',
            'acquisition_value' => 5000000,
            'useful_life_years' => 5,
        ]);
        $response->assertRedirect('/assets');

        $aset = Aset::where('kode_aset', 'TEST-AST-001')->first();
        $this->assertNotNull($aset);

        // 2. View Asset Show
        $response = $this->actingAs($user)->get('/assets/' . $aset->id);
        $response->assertStatus(200);

        // 3. Edit Asset
        $response = $this->actingAs($user)->get('/assets/' . $aset->id . '/edit');
        $response->assertStatus(200);

        // 4. Update Asset
        $response = $this->actingAs($user)->put('/assets/' . $aset->id, [
            'asset_code' => 'TEST-AST-001',
            'name' => 'Printer HP LaserJet Pro Updated',
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

        // 2. Store Out FIFO
        $response = $this->actingAs($user)->post('/inventory/out', [
            'inventory_item_id' => $persediaan->id,
            'qty_out' => 5,
        ]);
        $response->assertRedirect('/inventory');
    }

    public function test_authenticated_user_can_create_opname_session(): void
    {
        $this->seed();
        $user = User::where('role', 'admin')->first() ?? User::first();
        $ruangan = Ruangan::first();

        $response = $this->actingAs($user)->post('/opname', [
            'ruangan_id' => $ruangan->id,
            'tanggal' => date('Y-m-d'),
            'petugas' => 'Petugas KSOP Test',
            'keterangan' => 'Opname rutin mingguan',
        ]);
        $response->assertRedirect('/opname');
    }
}
