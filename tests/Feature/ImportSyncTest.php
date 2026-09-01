<?php

namespace Tests\Feature;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_operator_can_access_import_page(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $operator = User::where('role', 'operator')->first();
        $viewer = User::where('role', 'viewer')->first();

        // 1. Admin can access
        $response = $this->actingAs($admin)->get('/import');
        $response->assertStatus(200);

        // 2. Operator can access
        $response = $this->actingAs($operator)->get('/import');
        $response->assertStatus(200);

        // 3. Viewer is forbidden
        $response = $this->actingAs($viewer)->get('/import');
        $response->assertStatus(403);
    }

    public function test_admin_can_download_templates(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        // Download SAKTI template
        $response = $this->actingAs($admin)->get('/import/template/sakti');
        $response->assertStatus(200);
        $this->assertStringContainsString('Template_Import_Persediaan_SAKTI.xlsx', (string)$response->headers->get('content-disposition'));

        // Download SIMAN template
        $response = $this->actingAs($admin)->get('/import/template/siman');
        $response->assertStatus(200);
        $this->assertStringContainsString('Template_Import_Aset_SIMAN.xlsx', (string)$response->headers->get('content-disposition'));
    }

    public function test_admin_can_import_sakti_inventory_data(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $ruangan = Ruangan::first();

        // Buat file CSV dummy SAKTI
        $csvContent = "No,Kode Barang,Nama Barang,Kategori,Satuan,Saldo Stok,Harga Satuan,Stok Minimum\n";
        $csvContent .= "1,1.01.01.04.001.000099,BBM SOLAR KNP 333,Bahan Bakar,LITER,2500,14500,500\n";
        $csvContent .= "2,1.01.03.01.014.000088,BUKU LOG KAPAL PATROLI,Dokumen,BUAH,80,75000,10\n";

        $file = UploadedFile::fake()->createWithContent('laporan_sakti_test.csv', $csvContent);

        $response = $this->actingAs($admin)->post('/import/sakti', [
            'file_sakti' => $file,
            'ruangan_id' => $ruangan->id,
        ]);

        $response->assertRedirect('/import');
        $response->assertSessionHas('success');

        // Verifikasi data masuk ke database
        $solar = Persediaan::whereHas('jenisBarang', function ($q) {
            $q->where('nama_generik', 'like', '%BBM SOLAR KNP 333%');
        })->first();

        $this->assertNotNull($solar);
        $this->assertEquals('LITER', $solar->satuan);
        $this->assertEquals(2500, $solar->batches->sum('sisa_stok'));

        // Verifikasi audit log
        $this->assertTrue(AuditLog::where('aksi', 'Import SAKTI')->exists());
    }

    public function test_admin_can_import_siman_asset_data(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $ruangan = Ruangan::first();

        // Buat file CSV dummy SIMAN
        $csvContent = "No,Kode Aset,Kodefikasi BMN,NUP,Nama Barang,Merk,No Seri,Kategori,Kondisi,Nilai Perolehan,Tgl Perolehan,Masa Manfaat,Penanggung Jawab\n";
        $csvContent .= "1,AST-SIMAN-999,3.05.01.05.001,5,Laptop Dell Latitude 5420,Dell 5420,SN-DELL-998811,Elektronik,Baik,18000000,2024-01-10,5,Budi Santoso\n";

        $file = UploadedFile::fake()->createWithContent('laporan_siman_test.csv', $csvContent);

        $response = $this->actingAs($admin)->post('/import/siman', [
            'file_siman' => $file,
            'ruangan_id' => $ruangan->id,
        ]);

        $response->assertRedirect('/import');
        $response->assertSessionHas('success');

        // Verifikasi aset masuk ke database
        $aset = Aset::where('kode_aset', 'AST-SIMAN-999')->first();
        $this->assertNotNull($aset);
        $this->assertEquals('3.05.01.05.001', $aset->kode_bmn);
        $this->assertEquals(5, $aset->nup);
        $this->assertEquals('SN-DELL-998811', $aset->no_seri);
        $this->assertEquals(18000000, $aset->nilai_perolehan);
        $this->assertEquals('Budi Santoso', $aset->penanggung_jawab);

        // Verifikasi penyusutan SIMAN terkalkulasi otomatis
        $susut = $aset->hitungPenyusutanGarisLurus();
        $this->assertEquals(10, $susut['total_semester']); // 5 tahun * 2 semester
        $this->assertEquals(1800000, $susut['susut_per_semester']); // 18jt / 10
    }
}
