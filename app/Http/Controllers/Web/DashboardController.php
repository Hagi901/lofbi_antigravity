<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $asets = Aset::with(['jenisBarang.kategori', 'ruangan'])->get();
        $totalAset = $asets->count();
        $totalNilaiBuku = $asets->sum(fn ($a) => $a->nilai_buku_dinamis);

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

        // Data chart mutasi 6 bulan terakhir (berakhir di bulan berjalan)
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ags',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $chartLabels = [];
        $chartMasuk = [];
        $chartKeluar = [];

        $startPeriod = now()->startOfMonth()->subMonths(5);
        $transaksis = TransaksiPersediaan::where('tanggal', '>=', $startPeriod->toDateString())
            ->where(function ($q) {
                $q->where('jenis', 'masuk')
                    ->orWhere(function ($q2) {
                        $q2->where('jenis', 'keluar')->where('status', 'disetujui');
                    });
            })
            ->get();

        $batches = BatchPersediaan::where('tanggal_masuk', '>=', $startPeriod->toDateString())
            ->whereNotNull('upload_baris_id')
            ->get();

        for ($i = 5; $i >= 0; $i--) {
            $monthCarbon = now()->startOfMonth()->subMonths($i);
            $yearMonth = $monthCarbon->format('Y-m');
            $chartLabels[] = $namaBulan[(int) $monthCarbon->format('n')];

            $totalMasukTransaksi = (int) $transaksis->filter(function ($t) use ($yearMonth) {
                return $t->jenis === 'masuk' && Carbon::parse($t->tanggal)->format('Y-m') === $yearMonth;
            })->sum('jumlah');

            $totalMasukBatch = (int) $batches->filter(function ($b) use ($yearMonth, $transaksis) {
                if (Carbon::parse($b->tanggal_masuk)->format('Y-m') !== $yearMonth) {
                    return false;
                }

                // Cegah hitung ganda bila batch sudah memiliki transaksi masuk terkait
                if ($b->no_referensi && $transaksis->where('jenis', 'masuk')->where('no_referensi', $b->no_referensi)->isNotEmpty()) {
                    return false;
                }

                return true;
            })->sum('jumlah_masuk');

            $totalMasuk = $totalMasukTransaksi + $totalMasukBatch;

            $totalKeluar = (int) $transaksis->filter(function ($t) use ($yearMonth) {
                return $t->jenis === 'keluar' && $t->status === 'disetujui' && Carbon::parse($t->tanggal)->format('Y-m') === $yearMonth;
            })->sum('jumlah');

            $chartMasuk[] = $totalMasuk;
            $chartKeluar[] = $totalKeluar;
        }

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
