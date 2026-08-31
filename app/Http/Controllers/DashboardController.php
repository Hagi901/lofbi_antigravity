<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $asets = Aset::with(['jenisBarang.kategori', 'ruangan'])->get();
        $totalAset = $asets->count();
        $totalNilaiBuku = $asets->sum(fn($a) => $a->nilai_buku_dinamis);

        $persediaans = Persediaan::with(['jenisBarang', 'batches'])->get();
        $totalPersediaan = $persediaans->count();
        $totalStokPersediaan = (int) BatchPersediaan::sum('sisa_stok');

        $lowStockItems = $persediaans->filter(function ($p) {
            $sisa = $p->batches->sum('sisa_stok');
            return $sisa <= ($p->stok_minimum ?? 0);
        });
        $stokMenipis = $lowStockItems->count();

        $pengajuanMenunggu = TransaksiPersediaan::where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->count();

        $asetRusak = $asets->whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count();
        $asetBaik = $asets->where('kondisi', 'baik')->count();

        $recentLogs = AuditLog::latest()->take(5)->get();

        // Data chart mutasi 6 bulan terakhir
        $chartLabels = ['Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags'];
        $chartMasuk  = [10, 25, 50, 40, 30, (int) TransaksiPersediaan::where('jenis', 'masuk')->sum('jumlah')];
        $chartKeluar = [5, 12, 20, 15, 25, (int) TransaksiPersediaan::where('jenis', 'keluar')->where('status', 'disetujui')->sum('jumlah')];

        return view('dashboard', compact(
            'totalAset',
            'totalNilaiBuku',
            'totalPersediaan',
            'totalStokPersediaan',
            'stokMenipis',
            'lowStockItems',
            'pengajuanMenunggu',
            'asetRusak',
            'asetBaik',
            'recentLogs',
            'chartLabels',
            'chartMasuk',
            'chartKeluar'
        ));
    }
}
