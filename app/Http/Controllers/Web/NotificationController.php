<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\Notifikasi;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Notifikasi sistem (upload_baru, ada_ditolak, disetujui, dsb) untuk user ini
        $notifikasiSistem = Notifikasi::where('penerima_id', $user->id)
            ->latest()
            ->paginate(20);

        // 2. Pengajuan keluar menunggu
        $pengajuanMenunggu = TransaksiPersediaan::with(['persediaan.jenisBarang', 'diajukanOleh'])
            ->where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        // 3. Stok menipis / habis
        $stokMenipis = Persediaan::with(['jenisBarang', 'batches'])
            ->get()
            ->filter(function ($item) {
                $sisa = $item->batches->sum('sisa_stok');
                return $sisa <= ($item->stok_minimum ?? 0);
            });

        // 4. Aset habis umur ekonomis (persen_susut >= 100%)
        $asetHabisUmur = Aset::with(['jenisBarang', 'ruangan'])
            ->get()
            ->filter(function ($aset) {
                return $aset->persen_susut >= 100;
            });

        // 5. Log aktivitas terbaru
        $recentLogs = AuditLog::latest()->take(10)->get();

        return view('notifications', compact('notifikasiSistem', 'pengajuanMenunggu', 'stokMenipis', 'asetHabisUmur', 'recentLogs'));
    }

    /**
     * Tandai semua notifikasi user ini sebagai sudah dibaca.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Notifikasi::where('penerima_id', $user->id)
                ->whereNull('dibaca_pada')
                ->update(['dibaca_pada' => now()]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca dan arahkan ke tautan tujuan jika ada.
     */
    public function markRead($id, Request $request)
    {
        $notif = Notifikasi::where('penerima_id', Auth::id())->findOrFail($id);
        $notif->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if ($notif->tautan) {
            return redirect($notif->tautan);
        }

        return back();
    }
}