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
            'alamat_instansi' => $settings['alamat_instansi'] ?? 'Jl. Raya Pelabuhan No. 1, Banten',
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
     * Backup data sistem. Mendukung SQLite (copy file) dan MySQL (mysqldump),
     * menyesuaikan DB_CONNECTION yang sedang aktif di .env.
     */
    public function backup()
    {
        $koneksi = config('database.default');

        return match ($koneksi) {
            'sqlite' => $this->backupSqlite(),
            'mysql' => $this->backupMysql(),
            default => response()->json([
                'message' => "Backup gagal: koneksi database '{$koneksi}' belum didukung fitur backup otomatis.",
            ], 422),
        };
    }

    private function backupSqlite()
    {
        $dbPath = database_path('database.sqlite');

        if (! file_exists($dbPath)) {
            return response()->json([
                'message' => 'Backup gagal: file database.sqlite tidak ditemukan di server.',
            ], 422);
        }

        $direktoriBackup = $this->siapkanDirektoriBackup();
        $namaFile = 'backup_lofbi_' . now()->format('Ymd_His') . '.sqlite';
        $tujuan = $direktoriBackup . DIRECTORY_SEPARATOR . $namaFile;

        copy($dbPath, $tujuan);

        return response()->json([
            'message' => 'Backup data berhasil dijalankan.',
            'timestamp' => now()->toDateTimeString(),
            'file_backup' => $namaFile,
            'ukuran_bytes' => filesize($tujuan),
        ]);
    }

    private function backupMysql()
    {
        $config = config('database.connections.mysql');
        $direktoriBackup = $this->siapkanDirektoriBackup();
        $namaFile = 'backup_lofbi_' . now()->format('Ymd_His') . '.sql';
        $tujuan = $direktoriBackup . DIRECTORY_SEPARATOR . $namaFile;

        $mysqldump = env('MYSQLDUMP_PATH', 'mysqldump');

        $perintah = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s > %s 2>&1',
            escapeshellarg($mysqldump),
            escapeshellarg($config['host']),
            escapeshellarg($config['port']),
            escapeshellarg($config['username']),
            $config['password'] !== '' ? '--password=' . escapeshellarg($config['password']) : '',
            escapeshellarg($config['database']),
            escapeshellarg($tujuan)
        );

        exec($perintah, $output, $kodeKeluar);

        if ($kodeKeluar !== 0 || ! file_exists($tujuan) || filesize($tujuan) === 0) {
            return response()->json([
                'message' => 'Backup gagal: perintah mysqldump tidak ditemukan atau gagal dijalankan. Atur MYSQLDUMP_PATH di .env jika mysqldump tidak ada di PATH sistem.',
                'detail' => implode("\n", $output),
            ], 500);
        }

        return response()->json([
            'message' => 'Backup data berhasil dijalankan.',
            'timestamp' => now()->toDateTimeString(),
            'file_backup' => $namaFile,
            'ukuran_bytes' => filesize($tujuan),
        ]);
    }

    private function siapkanDirektoriBackup(): string
    {
        $direktori = storage_path('app/backups');
        if (! is_dir($direktori)) {
            mkdir($direktori, 0755, true);
        }

        return $direktori;
    }
}