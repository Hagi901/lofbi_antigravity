<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->string('kode_bmn', 30)->nullable()->after('kode_aset');
            $table->unsignedInteger('nup')->default(1)->nullable()->after('kode_bmn');
            $table->string('no_seri')->nullable()->after('model');
            $table->string('penanggung_jawab')->nullable()->after('ruangan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['kode_bmn', 'nup', 'no_seri', 'penanggung_jawab']);
        });
    }
};
