<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend tabel batch_persediaans untuk mendukung:
     * - nullable harga_satuan (data SAKTI bahan_opname_fisik tidak memuat harga)
     * - harga_belum_diisi flag
     * - kode barang 16-digit string + pecahannya
     * - field dokumen SAKTI
     */
    public function up(): void
    {
        Schema::table('batch_persediaans', function (Blueprint $table) {
            // Ubah harga_satuan menjadi nullable
            // (harus drop index/unique dahulu jika ada - di sini tidak ada constraint khusus)
            $table->decimal('harga_satuan', 18, 2)->nullable()->change();

            if (!Schema::hasColumn('batch_persediaans', 'harga_belum_diisi')) {
                $table->boolean('harga_belum_diisi')->default(false)->after('harga_satuan');
            }

            // Kode barang 16 digit (SAKTI) sebagai STRING
            if (!Schema::hasColumn('batch_persediaans', 'kode_barang_lengkap')) {
                $table->string('kode_barang_lengkap', 20)->nullable()->after('harga_belum_diisi');
            }
            if (!Schema::hasColumn('batch_persediaans', 'kb_lvl1')) {
                $table->string('kb_lvl1', 5)->nullable()->after('kode_barang_lengkap');
            }
            if (!Schema::hasColumn('batch_persediaans', 'kb_lvl2')) {
                $table->string('kb_lvl2', 5)->nullable()->after('kb_lvl1');
            }
            if (!Schema::hasColumn('batch_persediaans', 'kb_lvl3')) {
                $table->string('kb_lvl3', 5)->nullable()->after('kb_lvl2');
            }
            if (!Schema::hasColumn('batch_persediaans', 'kb_lvl4')) {
                $table->string('kb_lvl4', 5)->nullable()->after('kb_lvl3');
            }
            if (!Schema::hasColumn('batch_persediaans', 'kb_lvl5')) {
                $table->string('kb_lvl5', 5)->nullable()->after('kb_lvl4');
            }
            if (!Schema::hasColumn('batch_persediaans', 'kb_seq')) {
                $table->string('kb_seq', 10)->nullable()->after('kb_lvl5');
            }

            // Field dokumen SAKTI (tersedia di Rincian Buku Persediaan)
            if (!Schema::hasColumn('batch_persediaans', 'no_dokumen_sakti')) {
                $table->string('no_dokumen_sakti', 100)->nullable()->after('kb_seq');
            }
            if (!Schema::hasColumn('batch_persediaans', 'no_bukti_sakti')) {
                $table->string('no_bukti_sakti', 100)->nullable()->after('no_dokumen_sakti');
            }
            if (!Schema::hasColumn('batch_persediaans', 'tgl_dok')) {
                $table->date('tgl_dok')->nullable()->after('no_bukti_sakti');
            }
            if (!Schema::hasColumn('batch_persediaans', 'tgl_buku')) {
                $table->date('tgl_buku')->nullable()->after('tgl_dok');
            }

            // Referensi ke upload_baris yang menghasilkan batch ini
            if (!Schema::hasColumn('batch_persediaans', 'upload_baris_id')) {
                $table->foreignId('upload_baris_id')
                    ->nullable()
                    ->constrained('upload_baris')
                    ->nullOnDelete()
                    ->after('tgl_buku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('batch_persediaans', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\UploadBaris::class);
            $table->dropColumn([
                'harga_belum_diisi',
                'kode_barang_lengkap',
                'kb_lvl1', 'kb_lvl2', 'kb_lvl3', 'kb_lvl4', 'kb_lvl5', 'kb_seq',
                'no_dokumen_sakti', 'no_bukti_sakti', 'tgl_dok', 'tgl_buku',
                'upload_baris_id',
            ]);
            // Kembalikan harga_satuan ke NOT NULL
            $table->decimal('harga_satuan', 18, 2)->nullable(false)->change();
        });
    }
};
