<?php

namespace Tests\Feature;

use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Notifikasi;
use App\Models\OpnameDetail;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\UploadBaris;
use App\Models\UploadPaket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadStagingValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $validator;
    protected User $viewer;
    protected Ruangan $ruangan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin     = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $this->operator  = User::where('role', 'operator')->first() ?? User::factory()->create(['role' => 'operator']);
        $this->validator = User::where('role', 'validator')->first() ?? User::factory()->create(['role' => 'validator']);
        $this->viewer    = User::where('role', 'viewer')->first() ?? User::factory()->create(['role' => 'viewer']);
        $this->ruangan   = Ruangan::first() ?? Ruangan::create(['nama' => 'Gudang Utama']);
    }

    /**
     * Helper membuat dummy berkas SAKTI bahan_opname_fisik dengan:
     * - Header utama
     * - Baris penomoran kolom (1, 2, 3...)
     * - Data normal (kode 16 digit string)
     * - Header berulang di tengah data (simulasi halaman laporan)
     * - Baris placeholder (nama / satuan = "-")
     */
    protected function createDummySaktiCsv(): UploadedFile
    {
        $csv = "Tanggal Perolehan,Kode Barang,Nama Barang,Satuan,Saldo di Sistem,Hasil Opsik,Kategori\n";
        // Baris nomor kolom
        $csv .= "1,2,3,4,5,6,7\n";
        // Baris data 1: Buku Pelaut (16 digit)
        $csv .= ",1010301014000037,BUKU PELAUT HUBLA,BUAH,150,,Dokumen Pelaut\n";
        // Baris data 2: BBM Solar
        $csv .= ",1010104001000013,BBM SOLAR KAPAL,LITER,500,,Bahan Bakar\n";
        // Header berulang di tengah
        $csv .= "Tanggal Perolehan,Kode Barang,Nama Barang,Satuan,Saldo di Sistem,Hasil Opsik,Kategori\n";
        $csv .= "1,2,3,4,5,6,7\n";
        // Baris data 3: Placeholder
        $csv .= ",1010108047000099,-,-,0,,-\n";

        return UploadedFile::fake()->createWithContent('bahan_opname_fisik_test.csv', $csv);
    }

    public function test_admin_and_operator_can_upload_to_staging(): void
    {
        $file = $this->createDummySaktiCsv();

        $response = $this->actingAs($this->operator)->post('/upload', [
            'file'       => $file,
            'ruangan_id' => $this->ruangan->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('upload_pakets', [
            'diunggah_oleh' => $this->operator->id,
            'status'        => 'menunggu',
            'jumlah_baris'  => 3,
        ]);

        // Verifikasi TIDAK langsung masuk ke batch_persediaans resmi
        $this->assertDatabaseMissing('batch_persediaans', [
            'kode_barang_lengkap' => '1010301014000037',
        ]);

        // Verifikasi notifikasi terkirim ke validator
        $this->assertDatabaseHas('notifikasis', [
            'penerima_id' => $this->validator->id,
            'jenis'       => 'upload_baru',
        ]);
    }

    public function test_viewer_is_forbidden_from_uploading(): void
    {
        $file = $this->createDummySaktiCsv();

        $response = $this->actingAs($this->viewer)->post('/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_unsupported_word_file_is_rejected_with_clear_message(): void
    {
        $docxFile = UploadedFile::fake()->create('rincian_buku_persediaan.docx', 500);

        $response = $this->actingAs($this->admin)->post('/upload', [
            'file' => $docxFile,
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak didukung', (string)session('error'));
    }

    public function test_staging_skips_repeated_headers_and_flags_placeholders(): void
    {
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->admin)->post('/upload', ['file' => $file]);

        $paket = UploadPaket::latest()->first();
        $this->assertNotNull($paket);
        $this->assertEquals(3, $paket->jumlah_baris);

        // Baris 1: Buku Pelaut
        $baris1 = $paket->baris()->where('kode_barang_lengkap', '1010301014000037')->first();
        $this->assertNotNull($baris1);
        $this->assertEquals('1', $baris1->kb_lvl1);
        $this->assertEquals('01', $baris1->kb_lvl2);
        $this->assertEquals('03', $baris1->kb_lvl3);
        $this->assertEquals('01', $baris1->kb_lvl4);
        $this->assertEquals('014', $baris1->kb_lvl5);
        $this->assertEquals('000037', $baris1->kb_seq);
        $this->assertTrue($baris1->harga_belum_diisi);
        $this->assertNull($baris1->harga_satuan);
        $this->assertFalse($baris1->is_placeholder);

        // Baris 3: Placeholder '-'
        $baris3 = $paket->baris()->where('kode_barang_lengkap', '1010108047000099')->first();
        $this->assertNotNull($baris3);
        $this->assertTrue($baris3->is_placeholder);
    }

    public function test_validator_can_reject_row_with_mandatory_description(): void
    {
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->admin)->post('/upload', ['file' => $file]);
        $paket = UploadPaket::latest()->first();
        $baris = $paket->baris()->first();

        // 1. Coba tolak tanpa deskripsi revisi -> harus error
        $response = $this->actingAs($this->validator)->post("/upload/baris/{$baris->id}/tolak", [
            'deskripsi_revisi' => '',
        ]);
        $response->assertSessionHasErrors('deskripsi_revisi');

        // 2. Tolak dengan deskripsi revisi
        $response = $this->actingAs($this->validator)->post("/upload/baris/{$baris->id}/tolak", [
            'deskripsi_revisi' => 'Satuan barang salah, harap perbaiki menjadi RIM',
        ]);
        $response->assertRedirect();

        $baris->refresh();
        $this->assertEquals('ditolak', $baris->status);
        $this->assertEquals('Satuan barang salah, harap perbaiki menjadi RIM', $baris->deskripsi_revisi);

        // Notifikasi ke pengunggah
        $this->assertDatabaseHas('notifikasis', [
            'penerima_id' => $paket->diunggah_oleh,
            'jenis'       => 'ada_ditolak',
        ]);
    }

    public function test_validator_approval_promotes_row_to_official_batch_with_null_price(): void
    {
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->admin)->post('/upload', ['file' => $file]);
        $paket = UploadPaket::latest()->first();
        $baris = $paket->baris()->where('kode_barang_lengkap', '1010301014000037')->first();

        $response = $this->actingAs($this->validator)->post("/upload/baris/{$baris->id}/setujui");
        $response->assertRedirect();

        $baris->refresh();
        $this->assertEquals('disetujui', $baris->status);
        $this->assertNotNull($baris->batch_persediaan_id);

        // Verifikasi masuk ke batch_persediaans resmi
        $batch = BatchPersediaan::find($baris->batch_persediaan_id);
        $this->assertNotNull($batch);
        $this->assertEquals(150, $batch->jumlah_masuk);
        $this->assertEquals(150, $batch->sisa_stok);
        $this->assertNull($batch->harga_satuan);
        $this->assertTrue($batch->harga_belum_diisi);
        $this->assertEquals('1010301014000037', $batch->kode_barang_lengkap);

        // Status paket menjadi disetujui_sebagian karena baris lain masih menunggu
        $paket->refresh();
        $this->assertEquals('disetujui_sebagian', $paket->status);
    }

    public function test_revision_replaces_rejected_row_and_preserves_approved_row(): void
    {
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->admin)->post('/upload', ['file' => $file]);
        $paket = UploadPaket::latest()->first();

        $barisDisetujui = $paket->baris()->where('kode_barang_lengkap', '1010301014000037')->first();
        $barisDitolak   = $paket->baris()->where('kode_barang_lengkap', '1010104001000013')->first();

        // Setujui baris 1
        $this->actingAs($this->validator)->post("/upload/baris/{$barisDisetujui->id}/setujui");
        $originalBatchId = $barisDisetujui->fresh()->batch_persediaan_id;

        // Tolak baris 2
        $this->actingAs($this->validator)->post("/upload/baris/{$barisDitolak->id}/tolak", [
            'deskripsi_revisi' => 'Saldo sistem harus 750 bukan 500',
        ]);

        $this->assertEquals('ditolak', $barisDitolak->fresh()->status);

        // Buat file revisi: baris 2 diperbaiki saldo 750
        $revCsv = "Tanggal Perolehan,Kode Barang,Nama Barang,Satuan,Saldo di Sistem,Hasil Opsik,Kategori\n";
        $revCsv .= "1,2,3,4,5,6,7\n";
        $revCsv .= ",1010301014000037,BUKU PELAUT HUBLA,BUAH,150,,Dokumen Pelaut\n";
        $revCsv .= ",1010104001000013,BBM SOLAR KAPAL,LITER,750,,Bahan Bakar\n"; // revisi saldo
        $revFile = UploadedFile::fake()->createWithContent('revisi_bahan.csv', $revCsv);

        // Upload revisi
        $response = $this->actingAs($this->admin)->post("/upload/{$paket->id}/revisi", [
            'file' => $revFile,
        ]);
        $response->assertRedirect();

        // 1. Baris yang disetujui TIDAK berubah dan batch-nya tetap sama (tidak terduplikasi)
        $barisDisetujui->refresh();
        $this->assertEquals('disetujui', $barisDisetujui->status);
        $this->assertEquals($originalBatchId, $barisDisetujui->batch_persediaan_id);
        $this->assertEquals(1, BatchPersediaan::where('kode_barang_lengkap', '1010301014000037')->count());

        // 2. Baris yang ditolak DIGANTIKAN dengan data baru dan kembali status 'menunggu'
        $barisDitolak->refresh();
        $this->assertEquals('menunggu', $barisDitolak->status);
        $this->assertEquals(750, $barisDitolak->saldo_sistem);
        $this->assertNull($barisDitolak->deskripsi_revisi);
    }

    public function test_validator_can_approve_all_pending_rows_at_once(): void
    {
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->admin)->post('/upload', ['file' => $file]);
        $paket = UploadPaket::latest()->first();

        // Setujui semua baris sekaligus
        $response = $this->actingAs($this->validator)->post("/upload/{$paket->id}/setujui-semua");
        $response->assertRedirect();

        $paket->refresh();
        $this->assertEquals('disetujui', $paket->status);
        $this->assertEquals(0, $paket->baris()->where('status', 'menunggu')->count());
        $this->assertEquals(3, $paket->baris()->where('status', 'disetujui')->count());
    }

    public function test_operator_cannot_validate_or_approve_staging_rows(): void
    {
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->operator)->post('/upload', ['file' => $file]);
        $paket = UploadPaket::latest()->first();
        $baris = $paket->baris()->first();

        // 1. Operator mencoba setujui baris -> 403 Forbidden
        $response = $this->actingAs($this->operator)->post("/upload/baris/{$baris->id}/setujui");
        $response->assertStatus(403);

        // 2. Operator mencoba tolak baris -> 403 Forbidden
        $response = $this->actingAs($this->operator)->post("/upload/baris/{$baris->id}/tolak", [
            'deskripsi_revisi' => 'Harus ditolak',
        ]);
        $response->assertStatus(403);

        // 3. Operator mencoba setujui semua baris -> 403 Forbidden
        $response = $this->actingAs($this->operator)->post("/upload/{$paket->id}/setujui-semua");
        $response->assertStatus(403);
    }

    public function test_notifications_sent_to_correct_recipients_for_all_three_types(): void
    {
        Notifikasi::truncate();

        // 1. Tipe 'upload_baru': Operator mengunggah berkas -> notifikasi terkirim ke Validator
        $file = $this->createDummySaktiCsv();
        $this->actingAs($this->operator)->post('/upload', ['file' => $file]);
        $paket = UploadPaket::latest()->first();

        $notifBaru = Notifikasi::where('jenis', 'upload_baru')->first();
        $this->assertNotNull($notifBaru);
        $this->assertEquals($this->validator->id, $notifBaru->penerima_id);
        $this->assertNotEquals($this->operator->id, $notifBaru->penerima_id);

        $barisDitolak   = $paket->baris()->first();
        $barisDisetujui = $paket->baris()->skip(1)->first();

        // 2. Tipe 'ada_ditolak': Validator menolak baris -> notifikasi terkirim ke Pengunggah (Operator)
        $this->actingAs($this->validator)->post("/upload/baris/{$barisDitolak->id}/tolak", [
            'deskripsi_revisi' => 'Kode barang perlu dikoreksi',
        ]);

        $notifDitolak = Notifikasi::where('jenis', 'ada_ditolak')->first();
        $this->assertNotNull($notifDitolak);
        $this->assertEquals($this->operator->id, $notifDitolak->penerima_id);
        $this->assertStringContainsString('Kode barang perlu dikoreksi', $notifDitolak->pesan);

        // 3. Tipe 'disetujui': Validator menyetujui baris -> notifikasi terkirim ke Pengunggah (Operator)
        $this->actingAs($this->validator)->post("/upload/baris/{$barisDisetujui->id}/setujui");

        $notifDisetujui = Notifikasi::where('jenis', 'disetujui')->first();
        $this->assertNotNull($notifDisetujui);
        $this->assertEquals($this->operator->id, $notifDisetujui->penerima_id);
        $this->assertStringContainsString('disetujui', strtolower($notifDisetujui->judul));
    }

    public function test_inventory_detail_excludes_null_price_and_shows_label_and_affected_count(): void
    {
        $kategori = Kategori::create(['nama' => 'Persediaan Khusus', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Kertas Segel BMN', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan'          => 'rim',
            'stok_minimum'    => 5,
            'ruangan_id'      => $this->ruangan->id,
        ]);

        // Batch 1: Ada harga (10 rim @ Rp 50.000 = Rp 500.000)
        BatchPersediaan::create([
            'persediaan_id'     => $persediaan->id,
            'no_batch'          => 1,
            'tanggal_masuk'     => '2026-01-01',
            'jumlah_masuk'      => 10,
            'harga_satuan'      => 50000,
            'harga_belum_diisi' => false,
            'sisa_stok'         => 10,
        ]);

        // Batch 2: Harga kosong / null dari opname fisik (20 rim @ NULL)
        BatchPersediaan::create([
            'persediaan_id'     => $persediaan->id,
            'no_batch'          => 2,
            'tanggal_masuk'     => '2026-02-01',
            'jumlah_masuk'      => 20,
            'harga_satuan'      => null,
            'harga_belum_diisi' => true,
            'sisa_stok'         => 20,
        ]);

        $response = $this->actingAs($this->admin)->get("/inventory/{$persediaan->id}");
        $response->assertStatus(200);

        // Total nilai harus Rp 500.000 (tidak nol atau error karena batch kedua null)
        $response->assertSee('500.000');
        // Label dan jumlah batch terdampak harus muncul di view
        $response->assertSee('(Nilai belum lengkap - 1 batch terdampak)');
    }

    public function test_opname_detail_excludes_null_price_and_shows_label_and_affected_count(): void
    {
        $kategori = Kategori::create(['nama' => 'ATK Uji', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Spidol Whiteboard', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan'          => 'buah',
            'stok_minimum'    => 5,
            'ruangan_id'      => $this->ruangan->id,
        ]);

        // Batch dengan harga kosong
        BatchPersediaan::create([
            'persediaan_id'     => $persediaan->id,
            'no_batch'          => 1,
            'tanggal_masuk'     => '2026-01-01',
            'jumlah_masuk'      => 15,
            'harga_satuan'      => null,
            'harga_belum_diisi' => true,
            'sisa_stok'         => 15,
        ]);

        $sesi = OpnameSesi::create([
            'ruangan_id' => $this->ruangan->id,
            'admin_id'   => $this->admin->id,
            'tanggal'    => '2026-08-01',
            'periode'    => 'Semester II 2026',
            'status'     => 'draft',
        ]);

        OpnameDetail::create([
            'opname_sesi_id' => $sesi->id,
            'persediaan_id'  => $persediaan->id,
            'stok_buku'      => 15,
            'stok_fisik'     => 15,
            'selisih'        => 0,
            'satuan'         => 'buah',
        ]);

        $response = $this->actingAs($this->admin)->get("/opname/{$sesi->id}");
        $response->assertStatus(200);
        $response->assertSee('(Nilai belum lengkap)');
        $response->assertSee('1');
    }
}

