<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // 1. Pengajuan keluar menunggu
        $pengajuanMenunggu = TransaksiPersediaan::with(['persediaan.jenisBarang', 'diajukanOleh'])
            ->where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        // 2. Stok menipis / habis
        $stokMenipis = Persediaan::with(['jenisBarang', 'batches'])
            ->get()
            ->filter(function ($item) {
                $sisa = $item->batches->sum('sisa_stok');
                return $sisa <= ($item->stok_minimum ?? 0);
            });

        // 3. Aset habis umur ekonomis (persen_susut >= 100%)
        $asetHabisUmur = Aset::with(['jenisBarang', 'ruangan'])
            ->get()
            ->filter(function ($aset) {
                return $aset->persen_susut >= 100;
            });

        // 4. Log aktivitas terbaru
        $recentLogs = AuditLog::latest()->take(10)->get();

        return view('notifications', compact('pengajuanMenunggu', 'stokMenipis', 'asetHabisUmur', 'recentLogs'));
    }
}