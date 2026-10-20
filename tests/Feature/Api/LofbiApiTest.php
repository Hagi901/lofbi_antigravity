<?php

namespace Tests\Feature\Api;

use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LofbiApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_access_me_endpoint(): void
    {
        User::factory()->create([
            'email' => 'admin@lofbi.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'admin@lofbi.test',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('access_token'))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@lofbi.test');
    }

    public function test_validator_approval_cuts_inventory_fifo_across_batches(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $validator = User::factory()->create(['role' => 'validator']);
        $kategori = Kategori::create(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Pulpen', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan' => 'pcs',
            'stok_minimum' => 5,
        ]);

        $batch1 = BatchPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'no_batch' => 1,
            'tanggal_masuk' => '2026-01-01',
            'jumlah_masuk' => 10,
            'harga_satuan' => 1000,
            'sisa_stok' => 3,
        ]);
        $batch2 = BatchPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'no_batch' => 2,
            'tanggal_masuk' => '2026-01-02',
            'jumlah_masuk' => 10,
            'harga_satuan' => 1200,
            'sisa_stok' => 10,
        ]);

        $pengajuan = TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => 8,
            'tanggal' => '2026-07-29',
            'unit_kerja_penerima' => 'TU',
            'diajukan_oleh' => $admin->id,
            'status' => 'menunggu',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => $validator->email,
            'password' => 'password',
        ])->json('access_token');

        $this->withToken($token)
            ->postJson("/api/persediaan/pengajuan/{$pengajuan->id}/setujui")
            ->assertOk()
            ->assertJsonPath('data.status', 'disetujui')
            ->assertJsonCount(2, 'data.detail_pemotongan');

        $this->assertSame(0, $batch1->fresh()->sisa_stok);
        $this->assertSame(5, $batch2->fresh()->sisa_stok);
    }

    public function test_rejecting_already_approved_pengajuan_returns_422_and_status_unchanged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $validator = User::factory()->create(['role' => 'validator']);
        $kategori = Kategori::create(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Kertas A4', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan' => 'rim',
            'stok_minimum' => 5,
        ]);

        $pengajuan = TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => 2,
            'tanggal' => '2026-07-29',
            'unit_kerja_penerima' => 'TU',
            'diajukan_oleh' => $admin->id,
            'diputuskan_oleh' => $validator->id,
            'status' => 'disetujui',
            'tanggal_keputusan' => now(),
        ]);

        $token = $this->postJson('/api/login', [
            'email' => $validator->email,
            'password' => 'password',
        ])->json('access_token');

        $this->withToken($token)
            ->postJson("/api/persediaan/pengajuan/{$pengajuan->id}/tolak", [
                'catatan_penolakan' => 'Batal kirim',
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertSame('disetujui', $pengajuan->fresh()->status);
    }

    public function test_operator_can_view_persediaan_and_create_pengajuan_keluar_via_api(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $kategori = Kategori::create(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Kertas A4', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan' => 'rim',
            'stok_minimum' => 5,
        ]);
        BatchPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'no_batch' => 1,
            'tanggal_masuk' => '2026-01-01',
            'jumlah_masuk' => 20,
            'harga_satuan' => 50000,
            'sisa_stok' => 20,
        ]);

        $token = $this->postJson('/api/login', [
            'email' => $operator->email,
            'password' => 'password',
        ])->json('access_token');

        // 1. Operator bisa melihat ringkasan persediaan
        $this->withToken($token)
            ->getJson('/api/persediaan/ringkas')
            ->assertOk();

        // 2. Operator bisa melihat ringkasan aset
        $this->withToken($token)
            ->getJson('/api/aset/ringkas')
            ->assertOk();

        // 3. Operator bisa membuat pengajuan barang keluar
        $this->withToken($token)
            ->postJson("/api/persediaan/{$persediaan->id}/pengajuan-keluar", [
                'jumlah' => 5,
                'tanggal' => '2026-08-10',
                'unit_kerja_penerima' => 'Seksi Kepegawaian',
            ])
            ->assertCreated();
    }

    public function test_pimpinan_cannot_mutate_data_via_api(): void
    {
        $pimpinan = User::factory()->create(['role' => 'pimpinan']);
        $kategori = Kategori::create(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Buku', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan' => 'buah',
            'stok_minimum' => 5,
        ]);

        $token = $this->postJson('/api/login', [
            'email' => $pimpinan->email,
            'password' => 'password',
        ])->json('access_token');

        // 1. Pimpinan bisa membaca data persediaan dan aset
        $this->withToken($token)->getJson('/api/persediaan/ringkas')->assertOk();
        $this->withToken($token)->getJson('/api/aset/ringkas')->assertOk();

        // 2. Pimpinan bisa dashboard summary dan laporan
        $this->withToken($token)->getJson('/api/dashboard/summary')->assertOk();
        $this->withToken($token)->getJson('/api/laporan/baop')->assertOk();

        // 3. Pimpinan DILARANG membuat pengajuan keluar (403)
        $this->withToken($token)
            ->postJson("/api/persediaan/{$persediaan->id}/pengajuan-keluar", [
                'jumlah' => 2,
                'tanggal' => '2026-08-10',
                'unit_kerja_penerima' => 'Unit Pimpinan',
            ])
            ->assertForbidden();

        // 4. Pimpinan DILARANG mengubah master persediaan (403)
        $this->withToken($token)
            ->putJson("/api/persediaan/{$persediaan->id}", ['satuan' => 'dus'])
            ->assertForbidden();

        // 5. Pimpinan DILARANG melihat daftar pengajuan (hanya admin & validator)
        $this->withToken($token)->getJson('/api/persediaan/pengajuan')->assertForbidden();

        // 6. Pimpinan DILARANG melihat riwayat opname
        $this->withToken($token)->getJson('/api/opname/riwayat')->assertForbidden();
    }

    public function test_operator_can_access_dashboard_summary_and_reports_api(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $token = $this->postJson('/api/login', [
            'email' => $operator->email,
            'password' => 'password',
        ])->json('access_token');

        // Operator dapat melihat ringkasan persediaan dan aset
        $this->withToken($token)->getJson('/api/persediaan/ringkas')->assertOk();
        $this->withToken($token)->getJson('/api/aset/ringkas')->assertOk();

        // Operator dapat melihat dashboard summary
        $this->withToken($token)->getJson('/api/dashboard/summary')->assertOk();

        // Operator dapat melihat laporan
        $this->withToken($token)->getJson('/api/laporan/baop')->assertOk();

        // Operator DILARANG melihat daftar pengajuan via API (hanya admin & validator)
        $this->withToken($token)->getJson('/api/persediaan/pengajuan')->assertForbidden();

        // Operator DILARANG mengakses settings
        $this->withToken($token)->getJson('/api/settings')->assertForbidden();
    }

    public function test_validator_can_see_pengajuan_list_but_cannot_create_pengajuan_keluar_via_api(): void
    {
        $validator = User::factory()->create(['role' => 'validator']);
        $kategori = Kategori::create(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Map Folio', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan' => 'buah',
            'stok_minimum' => 2,
        ]);

        $token = $this->postJson('/api/login', [
            'email' => $validator->email,
            'password' => 'password',
        ])->json('access_token');

        // Validator bisa membaca ringkasan
        $this->withToken($token)->getJson('/api/persediaan/ringkas')->assertOk();

        // Validator bisa melihat daftar pengajuan
        $this->withToken($token)->getJson('/api/persediaan/pengajuan')->assertOk();

        // Validator DILARANG membuat pengajuan keluar (hanya admin & operator)
        $this->withToken($token)
            ->postJson("/api/persediaan/{$persediaan->id}/pengajuan-keluar", [
                'jumlah' => 1,
                'tanggal' => '2026-08-10',
                'unit_kerja_penerima' => 'Seksi Test',
            ])
            ->assertForbidden();

        // Validator DILARANG mengakses settings
        $this->withToken($token)->getJson('/api/settings')->assertForbidden();
    }
}
