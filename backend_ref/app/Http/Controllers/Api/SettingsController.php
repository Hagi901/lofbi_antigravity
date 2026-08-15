<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Ambil semua pengaturan sistem.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return response()->json([
            'nama_ksop' => $settings['nama_ksop'] ?? 'KSOP Kelas I Banten',
            'alamat_instansi' => $settings['alamat_instansi'] ?? 'Jl. Yos Sudarso No. 1, Bandar Lampung',
            'logo_url' => $settings['logo_url'] ?? '/public/images/logo-ksop.png',
            'format_tanggal' => $settings['format_tanggal'] ?? 'DD MMM YYYY',
            'tahun_anggaran' => $settings['tahun_anggaran'] ?? '2026',
        ]);
    }

    /**
     * Update profil instansi & preferensi sistem.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_ksop' => ['sometimes', 'string', 'max:150'],
            'alamat_instansi' => ['sometimes', 'string'],
            'logo_url' => ['sometimes', 'string'],
            'format_tanggal' => ['sometimes', 'string'],
            'tahun_anggaran' => ['sometimes', 'string'],
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json([
            'message' => 'Pengaturan sistem berhasil diperbarui.',
            'settings' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    /**
     * Endpoint dummy backup data sistem.
     */
    public function backup()
    {
        return response()->json([
            'message' => 'Backup data berhasil dijalankan.',
            'timestamp' => now()->toDateTimeString(),
            'file_backup' => 'backup_lofbi_' . now()->format('Ymd_His') . '.sqlite',
        ]);
    }
}
