<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengajuanKeluarRequest;
use App\Http\Requests\StorePersediaanRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\JenisBarang;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use App\Services\Persediaan\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PersediaanController extends Controller
{
    public function __construct(
        protected PengajuanService $pengajuanService
    ) {}

    /**
     * Ringkasan persediaan dikelompokkan per jenis barang.
     * Dipaginasi 20 item per halaman.
     */
    public function ringkas()
    {
        return Persediaan::query()
            ->join('jenis_barangs', 'jenis_barangs.id', '=', 'persediaans.jenis_barang_id')
            ->leftJoin('kategoris', 'kategoris.id', '=', 'jenis_barangs.kategori_id')
            ->leftJoin('batch_persediaans', 'batch_persediaans.persediaan_id', '=', 'persediaans.id')
            ->selectRaw('jenis_barangs.id as jenis_barang_id, jenis_barangs.nama_generik, kategoris.nama as kategori, count(distinct persediaans.id) as jumlah_varian, coalesce(sum(batch_persediaans.sisa_stok), 0) as total_stok, min(persediaans.stok_minimum) as stok_minimum')
            ->groupBy('jenis_barangs.id', 'jenis_barangs.nama_generik', 'kategoris.nama')
            ->orderBy('jenis_barangs.nama_generik')
            ->paginate(20);
    }

    public function detailByJenis(JenisBarang $jenisBarang)
    {
        return Persediaan::with(['jenisBarang.kategori', 'ruangan', 'batches' => fn ($q) => $q->orderBy('no_batch')])
            ->where('jenis_barang_id', $jenisBarang->id)
            ->get();
    }

    public function show(Persediaan $persediaan)
    {
        return $persediaan->load(['jenisBarang.kategori', 'ruangan', 'batches' => fn ($q) => $q->orderBy('no_batch')]);
    }

    public function update(StorePersediaanRequest $request, Persediaan $persediaan)
    {
        $persediaan->update($request->validated());

        return $persediaan->load(['jenisBarang.kategori', 'ruangan']);
    }

    /**
     * Buat pengajuan barang keluar.
     * Validasi kecukupan stok dilakukan SEBELUM pengajuan dibuat,
     * bukan saat approval, sehingga pengajuan yang pasti gagal tidak lolos masuk.
     */
    public function pengajuanKeluar(PengajuanKeluarRequest $request, Persediaan $persediaan)
    {
        $request->ensureStokCukup($persediaan);

        $transaksi = $this->pengajuanService->ajukan(
            $persediaan,
            $request->validated(),
            $request->user(),
            false
        );

        return response()->json($transaksi, 201);
    }

    /**
     * Daftar pengajuan barang keluar. Dapat difilter dengan ?status=menunggu
     * Dipaginasi 20 item per halaman.
     */
    public function pengajuan(Request $request)
    {
        $pengajuans = TransaksiPersediaan::with(['persediaan.jenisBarang', 'detailPemotongan.batch'])
            ->where('jenis', 'keluar')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return PengajuanResource::collection($pengajuans);
    }

    /**
     * Validator / Admin menyetujui pengajuan barang keluar.
     * Stok dipotong secara FIFO dari batch paling awal.
     * Proteksi role dilakukan oleh middleware 'role:validator,admin' di route.
     */
    public function setujui(Request $request, TransaksiPersediaan $transaksi)
    {
        if ($transaksi->status !== 'menunggu' || $transaksi->jenis !== 'keluar') {
            return response()->json(['message' => 'Pengajuan tidak dapat diproses.'], 422);
        }

        try {
            $transaksi = $this->pengajuanService->setujui($transaksi, $request->user(), [
                'orderBy' => 'no_batch',
                'recordDetail' => true,
                'logAudit' => false,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Stok tidak mencukupi untuk menyetujui pengajuan.'], 422);
        }

        return new PengajuanResource(
            $transaksi->load(['detailPemotongan.batch', 'persediaan.jenisBarang'])
        );
    }

    /**
     * Validator / Admin menolak pengajuan barang keluar.
     * Catatan penolakan wajib diisi.
     * Proteksi role dilakukan oleh middleware 'role:validator,admin' di route.
     */
    public function tolak(Request $request, TransaksiPersediaan $transaksi)
    {
        $data = $request->validate([
            'catatan_penolakan' => ['required', 'string'],
        ]);

        try {
            $transaksi = $this->pengajuanService->tolak(
                $transaksi,
                $data['catatan_penolakan'],
                $request->user(),
                false
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors()['reject'][0] ?? 'Pengajuan tidak dapat diproses.',
            ], 422);
        }

        return new PengajuanResource($transaksi);
    }

    public function batch(Persediaan $persediaan)
    {
        return $persediaan->batches()->orderBy('no_batch')->get();
    }
}
