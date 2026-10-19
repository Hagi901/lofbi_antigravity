<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpnameRequest;
use App\Models\OpnameSesi;
use App\Models\Ruangan;
use App\Services\Opname\OpnameService;

class OpnameController extends Controller
{
    public function __construct(
        protected OpnameService $opnameService
    ) {}

    /**
     * Ambil daftar aset dan persediaan yang terdaftar di ruangan,
     * sebagai referensi untuk pengisian form opname.
     */
    public function ruangan(Ruangan $ruangan)
    {
        return $this->opnameService->getReferensiRuangan($ruangan);
    }

    /**
     * Simpan hasil sesi opname fisik.
     * Setiap detail wajib memiliki aset_id atau persediaan_id (tidak boleh keduanya null).
     */
    public function store(StoreOpnameRequest $request)
    {
        $sesi = $this->opnameService->simpanSesiApi($request->validated(), $request->user());

        return response()->json($sesi->load(['details', 'ruangan']), 201);
    }

    /**
     * Riwayat sesi opname. Dipaginasi 15 per halaman.
     * Detail tidak di-eager-load di sini untuk efisiensi;
     * gunakan endpoint show per sesi jika detail diperlukan.
     */
    public function riwayat()
    {
        return OpnameSesi::with('ruangan')
            ->latest('tanggal')
            ->paginate(15);
    }
}
