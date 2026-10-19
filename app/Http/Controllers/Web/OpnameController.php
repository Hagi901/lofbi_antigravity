<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\AuditLog;
use App\Models\OpnameDetail;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Services\Opname\OpnameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OpnameController extends Controller
{
    public function __construct(
        protected OpnameService $opnameService
    ) {}

    // ── 1. Daftar Sesi Opname ────────────────────────────────────────────

    public function index()
    {
        $sesi = OpnameSesi::with(['admin', 'approver', 'details'])
            ->latest()
            ->get();
        return view('opname.index', compact('sesi'));
    }

    // ── 2. Form Buka Sesi Baru ───────────────────────────────────────────

    public function create()
    {
        // Generate pilihan periode (Semester I & II untuk 2 tahun ke depan & ke belakang)
        $periodes = [];
        $tahunIni = (int) date('Y');
        for ($t = $tahunIni - 1; $t <= $tahunIni + 1; $t++) {
            $periodes[] = "Semester I $t";
            $periodes[] = "Semester II $t";
        }

        return view('opname.create', compact('periodes'));
    }

    // ── 3. Simpan Sesi + Snapshot Stok Buku ─────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'periode'    => 'required|string|max:50',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $data = [
            'ruangan_id' => $request->ruangan_id ?? null,
            'tanggal'    => $request->tanggal,
            'periode'    => $request->periode,
            'keterangan' => $request->keterangan,
        ];

        $this->opnameService->bukaSesiWeb($data, Auth::user());

        return redirect()->route('opname.index')
            ->with('success', 'Sesi Opname Fisik berhasil dibuka. Silakan input hasil hitung fisik.');
    }

    // ── 4. Detail Sesi Opname ────────────────────────────────────────────

    public function show($id)
    {
        $sesi = OpnameSesi::with([
            'admin', 'approver',
            'details.persediaan.jenisBarang',
            'details.persediaan.batches',
        ])->findOrFail($id);

        $metrik = $this->opnameService->hitungNilai($sesi);

        return view('opname.show', array_merge(['sesi' => $sesi], $metrik));
    }

    // ── 5. Form Input Fisik ──────────────────────────────────────────────

    public function inputFisik($id)
    {
        $sesi = OpnameSesi::with(['details.persediaan.jenisBarang'])
            ->findOrFail($id);

        // Hanya bisa input jika status draft atau ditolak
        if (!in_array($sesi->status, ['draft', 'ditolak'])) {
            return redirect()->route('opname.show', $id)
                ->with('error', 'Sesi ini tidak dapat diedit karena statusnya: ' . $sesi->statusLabel());
        }

        return view('opname.input_fisik', compact('sesi'));
    }

    // ── 6. Simpan Hasil Fisik + Hitung Selisih + Ajukan Persetujuan ─────

    public function saveFisik(Request $request, $id)
    {
        $sesi = OpnameSesi::findOrFail($id);

        if (!in_array($sesi->status, ['draft', 'ditolak'])) {
            return redirect()->route('opname.show', $id)
                ->with('error', 'Sesi ini tidak dapat diedit.');
        }

        $request->validate([
            'stok_fisik'   => 'required|array',
            'stok_fisik.*' => 'required|integer|min:0',
            'catatan'      => 'nullable|array',
        ]);

        try {
            $this->opnameService->simpanHasilFisik(
                $sesi,
                $request->stok_fisik,
                $request->catatan ?? [],
                Auth::user()
            );
        } catch (ValidationException $e) {
            return redirect()->route('opname.show', $id)
                ->with('error', $e->errors()['opname'][0] ?? $e->getMessage());
        }

        return redirect()->route('opname.show', $sesi->id)
            ->with('success', 'Hasil opname fisik berhasil disimpan dan diajukan ke Validator untuk disetujui.');
    }

    // ── 7. Persetujuan (Validator / KPA) ─────────────────────────────────

    public function approve(Request $request, $id)
    {
        $sesi = OpnameSesi::with('details.persediaan.batches')->findOrFail($id);

        try {
            $this->opnameService->setujui($sesi, Auth::user());
        } catch (ValidationException $e) {
            return redirect()->route('opname.show', $id)
                ->with('error', $e->errors()['opname'][0] ?? $e->getMessage());
        }

        return redirect()->route('opname.show', $id)
            ->with('success', 'Opname disetujui! Jurnal penyesuaian stok otomatis dibuat.');
    }

    // ── 8. Penolakan ─────────────────────────────────────────────────────

    public function reject(Request $request, $id)
    {
        $sesi = OpnameSesi::findOrFail($id);

        $request->validate([
            'catatan_penolakan' => 'required|string|max:500',
        ]);

        try {
            $this->opnameService->tolak($sesi, $request->catatan_penolakan, Auth::user());
        } catch (ValidationException $e) {
            return redirect()->route('opname.show', $id)
                ->with('error', $e->errors()['opname'][0] ?? $e->getMessage());
        }

        return redirect()->route('opname.show', $id)
            ->with('error', 'Opname ditolak. Operator dapat memperbaiki dan mengajukan ulang.');
    }
}