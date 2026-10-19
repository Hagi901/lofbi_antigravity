<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel upload_pakets: satu record per file yang diunggah.
     * Status paket diturunkan dari status baris (lihat computed accessor di model).
     */
    public function up(): void
    {
        Schema::create('upload_pakets', function (Blueprint $table) {
            $table->id();

            // Pengunggah
            $table->foreignId('diunggah_oleh')
                ->constrained('users')
                ->cascadeOnDelete();

            // Jenis data: 'sakti' (persediaan) | 'siman' (aset, pending)
            $table->string('jenis', 20)->default('sakti');

            // Nama file asli dan path storage
            $table->string('nama_file', 255);
            $table->string('path_file', 500);

            // Jumlah total baris data (bukan header, bukan skip row)
            $table->unsignedInteger('jumlah_baris')->default(0);

            // Status paket: menunggu | disetujui_sebagian | disetujui | ditolak
            // Kolom ini di-cache untuk query efisien; di-refresh tiap ada aksi approve/reject
            $table->string('status', 30)->default('menunggu');

            $table->text('catatan')->nullable(); // catatan validator opsional

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_pakets');
    }
};
