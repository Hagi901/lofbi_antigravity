<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\OpnameDetail;
use App\Models\OpnameSesi;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpnameController extends Controller
{
    /**
     * Menampilkan riwayat sesi opname fisik
     */
    public function index()
    {
        $sesi = OpnameSesi::with(['ruangan', 'admin', 'details'])->latest()->get();
        return view('opname', compact('sesi'));
    }

    /**
     * Menampilkan form pembuatan sesi opname baru
     */
    public function create()
    {
        $rooms = Ruangan::all();
        return view('opname_create', compact('rooms'));
    }

    /**
     * Menyimpan sesi opname fisik dan memverifikasi barang di ruangan
     */
    public function store(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required|exists:ruangans,id',
            'tanggal' => 'required|date',
        ]);

        DB::transaction(function () use ($request) {
            $sesi = OpnameSesi::create([
                'ruangan_id' => $request->ruangan_id,
                'admin_id' => Auth::id() ?? 1,
                'tanggal' => $request->tanggal,
                'status' => 'selesai',
            ]);

            // Ambil seluruh aset di ruangan tersebut untuk diverifikasi
            $asets = Aset::where('ruangan_id', $request->ruangan_id)->get();
            foreach ($asets as $aset) {
                OpnameDetail::create([
                    'opname_sesi_id' => $sesi->id,
                    'aset_id' => $aset->id,
                    'kondisi_aktual' => $aset->kondisi,
                    'catatan' => $request->keterangan ?: 'Pemeriksaan opname fisik berkala KSOP Banten',
                ]);

                $aset->update(['last_opname_date' => $request->tanggal]);
            }

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'OPNAME_FISIK',
                'table_name' => 'opname_sesis',
                'record_id' => $sesi->id,
                'old_values' => null,
                'new_values' => [
                    'ruangan_id' => $request->ruangan_id,
                    'jumlah_aset_diverifikasi' => $asets->count(),
                    'tanggal' => $request->tanggal,
                ],
            ]);
        });

        return redirect()->route('opname.index')->with('success', 'Sesi Opname Fisik berhasil disimpan dan diverifikasi!');
    }
}