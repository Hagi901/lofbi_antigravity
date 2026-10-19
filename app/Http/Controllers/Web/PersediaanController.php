<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\TransaksiPersediaan;
use App\Services\Persediaan\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PersediaanController extends Controller
{
    public function __construct(
        protected PengajuanService $pengajuanService
    ) {}

    /**
     * Menampilkan daftar persediaan dan sisa stok FIFO
     */
    public function index()
    {
        $items = Persediaan::with(['jenisBarang.kategori', 'batches', 'ruangan'])->get();
        $pengajuanMenunggu = TransaksiPersediaan::where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->count();

        // Hitung ringkasan statistik
        $totalItems = $items->count();
        $totalStok = $items->sum(fn ($i) => $i->batches->sum('sisa_stok'));

        // Exclude NULL-harga atau harga_belum_diisi dari kalkulasi nilai rupiah
        $hasHargaBelumDiisi = BatchPersediaan::where('harga_belum_diisi', true)->orWhereNull('harga_satuan')->exists();
        $totalNilaiRupiah = $items->sum(fn ($i) => $i->batches->where('harga_belum_diisi', false)->whereNotNull('harga_satuan')->sum(fn ($b) => $b->sisa_stok * $b->harga_satuan));

        return view('inventory.index', compact('items', 'pengajuanMenunggu', 'totalItems', 'totalStok', 'totalNilaiRupiah', 'hasHargaBelumDiisi'));
    }

    /**
     * Tampilkan Kartu Buku Persediaan per Barang
     */
    public function show($id)
    {
        $persediaan = Persediaan::with(['jenisBarang.kategori', 'ruangan', 'batches', 'transaksis.diajukanOleh', 'transaksis.diputuskanOleh'])
            ->findOrFail($id);

        $sisaStok = $persediaan->batches->sum('sisa_stok');

        // Kecualikan batch dengan harga_belum_diisi=true atau harga_satuan NULL dari penjumlahan nilai
        $batchesBerharga = $persediaan->batches
            ->where('harga_belum_diisi', false)
            ->whereNotNull('harga_satuan');

        $totalNilaiRupiah = $batchesBerharga->sum(fn ($b) => $b->sisa_stok * $b->harga_satuan);

        $batchTerdampak = $persediaan->batches
            ->where(fn ($b) => $b->harga_belum_diisi || is_null($b->harga_satuan));

        $hasHargaBelumDiisi = $batchTerdampak->isNotEmpty();
        $jumlahTerdampak = $batchTerdampak->count();

        return view('inventory.show', compact(
            'persediaan',
            'sisaStok',
            'totalNilaiRupiah',
            'hasHargaBelumDiisi',
            'jumlahTerdampak'
        ));
    }

    /**
     * Form Edit Master Barang
     */
    public function edit($id)
    {
        $persediaan = Persediaan::with(['jenisBarang.kategori'])->findOrFail($id);
        $categories = Kategori::where('tipe', 'persediaan')->orWhereNull('tipe')->orderBy('nama')->get();
        if ($categories->isEmpty()) {
            $categories = Kategori::orderBy('nama')->get();
        }
        $rooms = Ruangan::orderBy('nama')->get();

        return view('inventory.edit', compact('persediaan', 'categories', 'rooms'));
    }

    /**
     * Simpan Perubahan Master Barang
     */
    public function update(Request $request, $id)
    {
        $persediaan = Persediaan::with('jenisBarang')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:kategoris,id',
            'satuan' => 'required|string|max:50',
            'merk' => 'nullable|string|max:100',
            'stok_minimum' => 'nullable|integer|min:0',
            'ruangan_id' => 'nullable|exists:ruangans,id',
        ]);

        DB::transaction(function () use ($persediaan, $request) {
            $persediaan->jenisBarang->update([
                'nama_generik' => $request->name,
                'kategori_id' => $request->category_id,
            ]);

            $persediaan->update([
                'merk' => $request->merk,
                'satuan' => strtoupper($request->satuan),
                'stok_minimum' => (int) ($request->stok_minimum ?? 0),
                'ruangan_id' => $request->ruangan_id,
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul' => 'Persediaan',
                'aksi' => 'Edit Master',
                'detail' => 'Memperbarui data master barang ID #'.$persediaan->id.': '.$request->name,
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Master barang '.$request->name.' berhasil diperbarui!');
    }

    /**
     * Hapus Master Barang
     */
    public function destroy($id)
    {
        $persediaan = Persediaan::with('jenisBarang')->findOrFail($id);
        $namaBarang = $persediaan->name;

        DB::transaction(function () use ($persediaan, $namaBarang) {
            $persediaan->batches()->delete();
            $persediaan->transaksis()->delete();
            $persediaan->delete();

            if ($persediaan->jenisBarang && $persediaan->jenisBarang->persediaans()->count() == 0) {
                $persediaan->jenisBarang->delete();
            }

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul' => 'Persediaan',
                'aksi' => 'Hapus Master',
                'detail' => 'Menghapus data persediaan: '.$namaBarang,
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Barang '.$namaBarang.' beserta riwayat batch-nya berhasil dihapus!');
    }

    // ── PENGAJUAN BARANG KELUAR (FIFO) ─────────────────────────────────────

    /**
     * Menampilkan form pengeluaran barang (buat pengajuan)
     */
    public function createOut()
    {
        $items = Persediaan::with(['jenisBarang', 'batches'])->get();

        return view('inventory.out', compact('items'));
    }

    /**
     * Simpan PENGAJUAN barang keluar - status "menunggu" dulu
     */
    public function storeOut(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:persediaans,id',
            'qty_out' => 'required|integer|min:1',
            'unit_kerja_penerima' => 'required|string|max:255',
        ]);

        $persediaan = Persediaan::findOrFail($request->inventory_item_id);
        $qtyDiminta = (int) $request->qty_out;

        $totalStok = (int) $persediaan->batches()->where('sisa_stok', '>', 0)->sum('sisa_stok');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors([
                'qty_out' => 'Gagal! Stok fisik tidak mencukupi. Total sisa stok saat ini hanya '.$totalStok.' '.$persediaan->satuan.'.',
            ]);
        }

        $this->pengajuanService->ajukan($persediaan, [
            'jumlah' => $qtyDiminta,
            'tanggal' => $request->tanggal,
            'unit_kerja_penerima' => $request->unit_kerja_penerima,
        ], Auth::user(), logAudit: true);

        return redirect()->route('inventory.index')->with('success', 'Pengajuan barang keluar sebanyak '.$qtyDiminta.' '.$persediaan->satuan.' berhasil diajukan! Menunggu persetujuan Validator.');
    }

    // ── FITUR VALIDATOR ─────────────────────────────────────────────────────

    /**
     * Halaman antrian pengajuan barang keluar untuk Validator
     */
    public function pengajuan()
    {
        $pengajuan = TransaksiPersediaan::with(['persediaan.jenisBarang', 'diajukanOleh'])
            ->where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        $riwayat = TransaksiPersediaan::with(['persediaan.jenisBarang', 'diajukanOleh', 'diputuskanOleh'])
            ->where('jenis', 'keluar')
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->latest()
            ->take(20)
            ->get();

        return view('inventory.pengajuan', compact('pengajuan', 'riwayat'));
    }

    /**
     * Validator menyetujui pengajuan - eksekusi potong stok FIFO
     */
    public function approve($id)
    {
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->findOrFail($id);
        $persediaan = $transaksi->persediaan;
        $qtyDiminta = (int) $transaksi->jumlah;

        $totalStok = (int) $persediaan->batches()->where('sisa_stok', '>', 0)->sum('sisa_stok');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors(['approve' => 'Stok tidak mencukupi! Sisa stok: '.$totalStok.' '.$persediaan->satuan.'. Tidak dapat disetujui.']);
        }

        try {
            $this->pengajuanService->setujui($transaksi, Auth::user(), [
                'orderBy' => ['tanggal_masuk' => 'asc', 'id' => 'asc'],
                'recordDetail' => false,
                'logAudit' => true,
            ]);
        } catch (ValidationException $ve) {
            return back()->withErrors(['approve' => $ve->getMessage()]);
        }

        return redirect()->route('inventory.pengajuan')->with('success', 'Pengajuan berhasil disetujui dan stok telah dikurangi secara FIFO!');
    }

    /**
     * Validator menolak pengajuan
     */
    public function reject(Request $request, $id)
    {
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->findOrFail($id);

        $this->pengajuanService->tolak(
            $transaksi,
            $request->alasan ?? 'Ditolak oleh Validator.',
            Auth::user(),
            logAudit: true
        );

        return redirect()->route('inventory.pengajuan')->with('success', 'Pengajuan berhasil ditolak.');
    }
}
