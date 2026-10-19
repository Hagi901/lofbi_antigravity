<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel notifikasis untuk notifikasi sistem alur validasi upload:
     * - penerima_id
     * - jenis: 'upload_baru' | 'ada_ditolak' | 'disetujui' | 'stok_minimum' | 'opname'
     * - judul, pesan, tautan
     * - data: json payload
     * - dibaca_pada: timestamp waktu dibaca (nullable)
     */
    public function up(): void
    {
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('penerima_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('jenis', 50); // upload_baru, ada_ditolak, disetujui
            $table->string('judul', 255);
            $table->text('pesan');
            $table->string('tautan', 500)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('dibaca_pada')->nullable();

            $table->timestamps();

            $table->index(['penerima_id', 'dibaca_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
