<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel upload_baris: satu record per baris data di file upload.
     * Baris ini adalah staging - belum masuk ke tabel resmi.
     * Setelah disetujui validator, data dipindahkan ke tabel resmi (persediaan/aset).
     */
    public function up(): void
    {
        Schema::create('upload_baris', function (Blueprint $table) {
            $table->id();

            $table->foreignId('upload_paket_id')
                ->constrained('upload_pakets')
                ->cascadeOnDelete();

            // Nomor baris dalam file (1-based, untuk tampilan validator)
            $table->unsignedInteger('nomor_baris');

            // Status: menunggu | disetujui | ditolak
            $table->string('status', 20)->default('menunggu');

            // Deskripsi revisi (wajib diisi saat menolak)
            $table->text('deskripsi_revisi')->nullable();

            // Validator yang bertindak + waktu aksi
            $table->foreignId('ditindak_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditindak_pada')->nullable();

            // ─── FIELD PERSEDIAAN SAKTI ────────────────────────────────────────

            // Kode barang 16 digit, disimpan sebagai STRING (jangan float/int)
            $table->string('kode_barang_lengkap', 20)->nullable();

            // Pecahan kode barang 5-tingkat + nomor urut
            $table->string('kb_lvl1', 5)->nullable();  // e.g. "1"
            $table->string('kb_lvl2', 5)->nullable();  // e.g. "01"
            $table->string('kb_lvl3', 5)->nullable();  // e.g. "01"
            $table->string('kb_lvl4', 5)->nullable();  // e.g. "08"
            $table->string('kb_lvl5', 5)->nullable();  // e.g. "047"
            $table->string('kb_seq', 10)->nullable();  // e.g. "000001"

            $table->string('nama_barang', 255)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->integer('saldo_sistem')->nullable();        // Saldo di Sistem dari SAKTI
            $table->integer('hasil_opsik')->nullable();         // Hasil Opname Fisik (opsional)
            $table->string('kategori_sakti', 100)->nullable();  // Kolom Kategori dari file

            // File SAKTI bahan_opname_fisik tidak memuat harga - selalu null
            $table->decimal('harga_satuan', 18, 2)->nullable();
            $table->boolean('harga_belum_diisi')->default(true);

            // Field dokumen SAKTI tambahan (tersedia di file Rincian Buku Persediaan)
            $table->string('no_dokumen', 100)->nullable();
            $table->string('no_bukti', 100)->nullable();
            $table->date('tgl_dok')->nullable();
            $table->date('tgl_buku')->nullable();

            // ─── FLAG KUALITAS DATA ────────────────────────────────────────────

            // Baris placeholder: nama atau satuan = "-"
            $table->boolean('is_placeholder')->default(false);

            // Baris yang perlu perhatian validator (saldo negatif, kode tidak valid, dsb)
            $table->boolean('perlu_perhatian')->default(false);
            $table->string('catatan_parser', 500)->nullable();

            // ─── REFERENSI KE TABEL RESMI (setelah disetujui) ─────────────────
            $table->foreignId('batch_persediaan_id')
                ->nullable()
                ->constrained('batch_persediaans')
                ->nullOnDelete();

            // Kunci unik untuk deteksi revisi: kode_barang_lengkap + no_dokumen
            // Index dibuat untuk kecepatan lookup saat re-upload
            $table->index(['upload_paket_id', 'kode_barang_lengkap', 'no_dokumen'], 'idx_baris_kunci_unik');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_baris');
    }
};
