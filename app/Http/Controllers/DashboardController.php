<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\BatchPersediaan;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAset = Aset::count();
        $totalNilaiBuku = (float) Aset::sum('nilai_buku');
        $totalPersediaan = Persediaan::count();
        $totalStokPersediaan = (int) BatchPersediaan::sum('sisa_stok');

        $stokMenipis = Persediaan::query()
            ->leftJoin('batch_persediaans', 'batch_persediaans.persediaan_id', '=', 'persediaans.id')
            ->selectRaw('persediaans.id, persediaans.stok_minimum, coalesce(sum(batch_persediaans.sisa_stok), 0) as total_stok')
            ->groupBy('persediaans.id', 'persediaans.stok_minimum')
            ->havingRaw('coalesce(sum(batch_persediaans.sisa_stok), 0) < persediaans.stok_minimum')
            ->count();

        $pengajuanMenunggu = TransaksiPersediaan::where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->count();

        $asetRusak = Aset::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count();
        $asetBaik = Aset::where('kondisi', 'baik')->count();

        $asetTerbaru = Aset::with(['jenisBarang', 'ruangan'])->latest()->take(5)->get();
        $transaksiTerbaru = TransaksiPersediaan::with(['persediaan.jenisBarang', 'user'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalAset',
            'totalNilaiBuku',
            'totalPersediaan',
            'totalStokPersediaan',
            'stokMenipis',
            'pengajuanMenunggu',
            'asetRusak',
            'asetBaik',
            'asetTerbaru',
            'transaksiTerbaru'
        ));
    }
}
