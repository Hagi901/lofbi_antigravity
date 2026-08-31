<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_aplikasi'    => 'nullable|string|max:255',
            'nama_ksop'        => 'nullable|string|max:255',
            'alamat_instansi'  => 'nullable|string|max:500',
            'peringatan_stok'  => 'nullable|string|in:0,1',
            'notif_opname'     => 'nullable|string|in:0,1',
            'laporan_harian'   => 'nullable|string|in:0,1',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value ?? '']);
        }

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Administrator',
            'modul' => 'Pengaturan',
            'aksi' => 'Update',
            'detail' => 'Memperbarui konfigurasi sistem dan instansi.',
        ]);

        return redirect()->route('settings.index')->with('success', 'Pengaturan sistem berhasil disimpan!');
    }

    public function backup()
    {
        $dbPath = database_path('database.sqlite');
        if (file_exists($dbPath)) {
            return response()->download($dbPath, 'LOFBI_Backup_' . date('Ymd_His') . '.sqlite');
        }
        return back()->with('error', 'File database tidak ditemukan.');
    }
}