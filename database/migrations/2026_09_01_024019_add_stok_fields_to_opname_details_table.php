<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opname_details', function (Blueprint $table) {
            // Snapshot stok buku sistem saat opname dibuka
            $table->unsignedInteger('stok_buku')->default(0)->after('persediaan_id');
            // Hasil hitung fisik petugas
            $table->unsignedInteger('stok_fisik')->nullable()->after('stok_buku');
            // Selisih = stok_fisik - stok_buku (bisa negatif → stored as integer)
            $table->integer('selisih')->nullable()->after('stok_fisik');
            // Satuan barang (snapshot)
            $table->string('satuan')->nullable()->after('selisih');
        });
    }

    public function down(): void
    {
        Schema::table('opname_details', function (Blueprint $table) {
            $table->dropColumn(['stok_buku', 'stok_fisik', 'selisih', 'satuan']);
        });
    }
};
