<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update table asets
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'sub_kategori')) {
                $table->string('sub_kategori', 100)->nullable()->after('jenis_barang_id');
            }
            if (!Schema::hasColumn('asets', 'masa_manfaat')) {
                $table->integer('masa_manfaat')->nullable()->after('ruangan_id');
            }
            if (!Schema::hasColumn('asets', 'metode_penyusutan')) {
                $table->string('metode_penyusutan', 50)->default('Garis Lurus')->after('masa_manfaat');
            }
            if (!Schema::hasColumn('asets', 'last_opname_date')) {
                $table->date('last_opname_date')->nullable()->after('terakhir_dihitung_semester');
            }
        });

        // 2. Update table batch_persediaans
        Schema::table('batch_persediaans', function (Blueprint $table) {
            if (!Schema::hasColumn('batch_persediaans', 'no_referensi')) {
                $table->string('no_referensi', 100)->nullable()->after('no_batch');
            }
            if (!Schema::hasColumn('batch_persediaans', 'no_faktur')) {
                $table->string('no_faktur', 100)->nullable()->after('no_referensi');
            }
            if (!Schema::hasColumn('batch_persediaans', 'nota_dinas')) {
                $table->string('nota_dinas', 100)->nullable()->after('no_faktur');
            }
            if (!Schema::hasColumn('batch_persediaans', 'supplier')) {
                $table->string('supplier', 150)->nullable()->after('nota_dinas');
            }
        });

        // 3. Create table audit_logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('user_name', 100)->nullable();
                $table->string('modul', 50);
                $table->string('aksi', 50); // Tambah, Edit, Hapus, Approve, Tolak, Transfer
                $table->text('detail')->nullable();
                $table->timestamps();
            });
        }

        // 4. Create table settings
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::table('batch_persediaans', function (Blueprint $table) {
            $table->dropColumn(['no_referensi', 'no_faktur', 'nota_dinas', 'supplier']);
        });
        Schema::table('asets', function (Blueprint $table) {
            $table->dropColumn(['sub_kategori', 'masa_manfaat', 'metode_penyusutan', 'last_opname_date']);
        });
    }
};
