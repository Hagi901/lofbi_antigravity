<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opname_sesis', function (Blueprint $table) {
            // Periode opname, contoh: "Semester I 2026"
            $table->string('periode')->nullable()->after('tanggal');
            // Keterangan/tujuan opname
            $table->text('keterangan')->nullable()->after('periode');
            // Approver (Validator / KPA)
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete()->after('keterangan');
            $table->date('tanggal_persetujuan')->nullable()->after('approver_id');
            $table->text('catatan_penolakan')->nullable()->after('tanggal_persetujuan');

            // Jadikan ruangan_id nullable karena opname persediaan mencakup seluruh satker/gudang
            $table->unsignedBigInteger('ruangan_id')->nullable()->change();

            // Ubah default status dari 'selesai' menjadi 'draft'
            $table->string('status')->default('draft')->change();
        });
    }


    public function down(): void
    {
        Schema::table('opname_sesis', function (Blueprint $table) {
            $table->dropForeign(['approver_id']);
            $table->dropColumn([
                'periode', 'keterangan', 'approver_id',
                'tanggal_persetujuan', 'catatan_penolakan',
            ]);
            $table->string('status')->default('selesai')->change();
        });
    }
};
