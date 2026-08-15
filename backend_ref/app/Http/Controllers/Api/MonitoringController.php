<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    /**
     * Tracking lokasi aset real-time dan status kondisi.
     */
    public function tracking(Request $request)
    {
        $asets = Aset::with(['jenisBarang', 'ruangan'])
            ->when($request->ruangan_id, fn ($q, $id) => $q->where('ruangan_id', $id))
            ->when($request->kondisi, fn ($q, $k) => $q->where('kondisi', $k))
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json($asets);
    }

    /**
     * Peringatan aset belum di-opname > 6 bulan.
     */
    public function peringatanOpname()
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6)->toDateString();

        $asetsBelumOpname = Aset::with(['jenisBarang', 'ruangan'])
            ->where(function ($q) use ($sixMonthsAgo) {
                $q->whereNull('last_opname_date')
                  ->orWhere('last_opname_date', '<=', $sixMonthsAgo);
            })
            ->get();

        return response()->json([
            'total_belum_opname' => $asetsBelumOpname->count(),
            'batas_tanggal' => $sixMonthsAgo,
            'data' => $asetsBelumOpname,
        ]);
    }

    /**
     * Log aktivitas sistem terbaru untuk monitoring.
     */
    public function logAktivitas()
    {
        $logs = AuditLog::latest()->take(15)->get();

        return response()->json($logs);
    }
}
