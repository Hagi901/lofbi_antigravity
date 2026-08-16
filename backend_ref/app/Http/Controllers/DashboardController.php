<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan stat cards
        $totalAset = Aset::count();
        $totalNilaiBuku = Aset::sum('nilai_buku');
        $pengajuanMenunggu = TransaksiPersediaan::where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->count();
        $asetRusak = Aset::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count();
        $totalJenisPersediaan = Persediaan::distinct('jenis_barang_id')->count('jenis_barang_id');
        $asetBaik = Aset::where('kondisi', 'baik')->count();
        $persenAsetBaik = $totalAset > 0 ? round(($asetBaik / $totalAset) * 100, 1) : 0;

        $stokMenipis = Persediaan::query()
            ->leftJoin('batch_persediaans', 'batch_persediaans.persediaan_id', '=', 'persediaans.id')
            ->selectRaw('persediaans.id, persediaans.stok_minimum, coalesce(sum(batch_persediaans.sisa_stok), 0) as total_stok')
            ->groupBy('persediaans.id', 'persediaans.stok_minimum')
            ->havingRaw('coalesce(sum(batch_persediaans.sisa_stok), 0) < persediaans.stok_minimum')
            ->count();

        // Distribusi kondisi aset (untuk chart)
        $distribusiKondisi = Aset::select('kondisi', DB::raw('count(*) as total'))
            ->groupBy('kondisi')
            ->pluck('total', 'kondisi');

        // Distribusi persediaan per jenis barang (untuk donut chart)
        $distribusiPersediaan = Persediaan::join('jenis_barangs', 'jenis_barangs.id', '=', 'persediaans.jenis_barang_id')
    ->select('jenis_barangs.nama_generik as label', DB::raw('count(persediaans.id) as total'))
    ->groupBy('jenis_barangs.nama_generik')
    ->pluck('total', 'label');

        // Transaksi/aktivitas terbaru
        $transaksiTerbaru = AuditLog::latest()->take(5)->get();

        return view('index', compact(
            'totalAset',
            'totalNilaiBuku',
            'pengajuanMenunggu',
            'asetRusak',
            'totalJenisPersediaan',
            'asetBaik',
            'persenAsetBaik',
            'stokMenipis',
            'distribusiKondisi',
            'distribusiPersediaan',
            'transaksiTerbaru'
        ));
    }
}