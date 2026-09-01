<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\OpnameDetail;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpnameController extends Controller
{
    // ── 1. Daftar Sesi Opname ────────────────────────────────────────────

    public function index()
    {
        $sesi = OpnameSesi::with(['admin', 'approver', 'details'])
            ->latest()
            ->get();
        return view('opname', compact('sesi'));
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

        return view('opname_create', compact('periodes'));
    }

    // ── 3. Simpan Sesi + Snapshot Stok Buku ─────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'periode'    => 'required|string|max:50',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            // Buat sesi baru dengan status 'draft'
            $sesi = OpnameSesi::create([
                'ruangan_id' => $request->ruangan_id ?? null,
                'admin_id'   => Auth::id() ?? 1,
                'tanggal'    => $request->tanggal,
                'periode'    => $request->periode,
                'keterangan' => $request->keterangan,
                'status'     => 'draft',
            ]);

            // Snapshot semua persediaan aktif beserta stok buku saat ini
            $persediaans = Persediaan::with('batches')->get();
            foreach ($persediaans as $p) {
                $stokBuku = (int) $p->batches->sum('sisa_stok');
                OpnameDetail::create([
                    'opname_sesi_id' => $sesi->id,
                    'persediaan_id'  => $p->id,
                    'stok_buku'      => $stokBuku,
                    'stok_fisik'     => null,   // diisi saat input fisik
                    'selisih'        => null,
                    'satuan'         => $p->satuan ?? '-',
                    'catatan'        => null,
                ]);
            }

            AuditLog::create([
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'modul'     => 'Opname',
                'aksi'      => 'Buka Sesi Opname',
                'detail'    => 'Buka sesi opname ' . $request->periode . ' — ' . $persediaans->count() . ' jenis barang di-snapshot',
            ]);
        });

        return redirect()->route('opname.index')
            ->with('success', 'Sesi Opname Fisik berhasil dibuka. Silakan input hasil hitung fisik.');
    }

    // ── 4. Detail Sesi Opname ────────────────────────────────────────────

    public function show($id)
    {
        $sesi = OpnameSesi::with([
            'admin', 'approver',
            'details.persediaan.jenisBarang',
        ])->findOrFail($id);

        return view('opname_show', compact('sesi'));
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

        DB::transaction(function () use ($request, $sesi) {
            foreach ($request->stok_fisik as $detailId => $jumlahFisik) {
                $detail = OpnameDetail::where('id', $detailId)
                    ->where('opname_sesi_id', $sesi->id)
                    ->firstOrFail();

                $selisih = (int) $jumlahFisik - (int) $detail->stok_buku;

                $detail->update([
                    'stok_fisik' => (int) $jumlahFisik,
                    'selisih'    => $selisih,
                    'catatan'    => $request->catatan[$detailId] ?? null,
                ]);
            }

            // Ubah status menjadi menunggu_persetujuan
            $sesi->update(['status' => 'menunggu_persetujuan']);

            AuditLog::create([
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'modul'     => 'Opname',
                'aksi'      => 'Submit Hasil Fisik',
                'detail'    => 'Sesi #' . $sesi->id . ' diajukan ke approver',
            ]);
        });

        return redirect()->route('opname.show', $sesi->id)
            ->with('success', 'Hasil opname fisik berhasil disimpan dan diajukan ke Validator untuk disetujui.');
    }

    // ── 7. Persetujuan (Validator / KPA) ─────────────────────────────────

    public function approve(Request $request, $id)
    {
        $sesi = OpnameSesi::with('details.persediaan.batches')->findOrFail($id);

        if ($sesi->status !== 'menunggu_persetujuan') {
            return redirect()->route('opname.show', $id)
                ->with('error', 'Sesi ini tidak dalam status menunggu persetujuan.');
        }

        DB::transaction(function () use ($sesi) {
            // Buat jurnal penyesuaian otomatis untuk setiap item yang ada selisih
            foreach ($sesi->details as $detail) {
                if ($detail->selisih === null || $detail->selisih === 0) continue;

                $p = $detail->persediaan;
                if (!$p) continue;

                // Hitung harga satuan rata-rata dari batch aktif
                $totalNilai = $p->batches->sum(fn($b) => $b->sisa_stok * $b->harga_satuan);
                $totalStok  = $p->batches->sum('sisa_stok');
                $hargaRata  = $totalStok > 0 ? round($totalNilai / $totalStok) : 0;

                TransaksiPersediaan::create([
                    'persediaan_id'      => $detail->persediaan_id,
                    'jenis'              => $detail->selisih > 0 ? 'masuk' : 'keluar',
                    'jumlah'             => abs($detail->selisih),
                    'tanggal'            => $sesi->tanggal,
                    'unit_kerja_penerima' => 'Penyesuaian Opname Fisik — ' . $sesi->periode,
                    'diajukan_oleh'      => $sesi->admin_id,
                    'diputuskan_oleh'    => Auth::id(),
                    'status'             => 'disetujui',
                    'catatan_penolakan'  => null,
                    'tanggal_keputusan'  => now(),
                ]);

                // Update sisa_stok batch FIFO jika selisih kurang
                if ($detail->selisih < 0) {
                    $kurang = abs($detail->selisih);
                    foreach ($p->batches()->orderBy('tanggal_masuk')->get() as $batch) {
                        if ($kurang <= 0) break;
                        $potongan = min($batch->sisa_stok, $kurang);
                        $batch->decrement('sisa_stok', $potongan);
                        $kurang -= $potongan;
                    }
                } elseif ($detail->selisih > 0) {
                    // Selisih lebih → tambahkan ke batch paling baru
                    $batchTerbaru = $p->batches()->latest('tanggal_masuk')->first();
                    if ($batchTerbaru) {
                        $batchTerbaru->increment('sisa_stok', $detail->selisih);
                    }
                }
            }

            // Tutup sesi
            $sesi->update([
                'status'              => 'disetujui',
                'approver_id'         => Auth::id(),
                'tanggal_persetujuan' => now()->toDateString(),
            ]);

            AuditLog::create([
                'user_id'   => Auth::id(),
                'user_name' => Auth::user()->name,
                'modul'     => 'Opname',
                'aksi'      => 'Setujui Opname',
                'detail'    => 'Sesi #' . $sesi->id . ' (' . $sesi->periode . ') disetujui. Jurnal penyesuaian dibuat.',
            ]);
        });

        return redirect()->route('opname.show', $id)
            ->with('success', 'Opname disetujui! Jurnal penyesuaian stok otomatis dibuat.');
    }

    // ── 8. Penolakan ─────────────────────────────────────────────────────

    public function reject(Request $request, $id)
    {
        $sesi = OpnameSesi::findOrFail($id);

        if ($sesi->status !== 'menunggu_persetujuan') {
            return redirect()->route('opname.show', $id)
                ->with('error', 'Sesi ini tidak dalam status menunggu persetujuan.');
        }

        $request->validate([
            'catatan_penolakan' => 'required|string|max:500',
        ]);

        $sesi->update([
            'status'            => 'ditolak',
            'catatan_penolakan' => $request->catatan_penolakan,
            'approver_id'       => Auth::id(),
        ]);

        AuditLog::create([
            'user_id'   => Auth::id(),
            'user_name' => Auth::user()->name,
            'modul'     => 'Opname',
            'aksi'      => 'Tolak Opname',
            'detail'    => 'Sesi #' . $sesi->id . ' ditolak: ' . $request->catatan_penolakan,
        ]);

        return redirect()->route('opname.show', $id)
            ->with('error', 'Opname ditolak. Operator dapat memperbaiki dan mengajukan ulang.');
    }
}