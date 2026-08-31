<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Menampilkan daftar persediaan dan sisa stok FIFO
     */
    public function index()
    {
        $items = Persediaan::with(['jenisBarang.kategori', 'batches'])->get();
        $pengajuanMenunggu = TransaksiPersediaan::where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->count();
        return view('inventory', compact('items', 'pengajuanMenunggu'));
    }

    /**
     * Menampilkan form pencatatan Barang Masuk
     */
    public function createIn()
    {
        $items = Persediaan::with('jenisBarang')->get();
        return view('inventory.in', compact('items'));
    }

    /**
     * Menyimpan data Barang Masuk & membuat batch baru
     */
    public function storeIn(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:persediaans,id',
            'qty_received' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        $persediaan = Persediaan::findOrFail($request->inventory_item_id);

        DB::transaction(function () use ($persediaan, $request) {
            $batchNumber = 'BATCH-' . date('YmdHis');

            BatchPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'no_batch' => $batchNumber,
                'no_referensi' => $request->no_referensi ?? ('REF-' . date('YmdHis')),
                'no_faktur' => $request->no_faktur ?? ('FAK-' . date('YmdHis')),
                'nota_dinas' => $request->nota_dinas ?? ('ND/' . date('Y/m')),
                'supplier' => $request->supplier ?? 'Penyedia Barang KSOP',
                'tanggal_masuk' => $request->tanggal ?? now()->toDateString(),
                'jumlah_masuk' => $request->qty_received,
                'harga_satuan' => $request->purchase_price,
                'sisa_stok' => $request->qty_received,
            ]);

            TransaksiPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'diajukan_oleh' => Auth::id() ?? 1,
                'jenis' => 'masuk',
                'jumlah' => $request->qty_received,
                'tanggal' => now()->toDateString(),
                'status' => 'disetujui',
                'unit_kerja_penerima' => 'Gudang Utama KSOP',
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul' => 'Persediaan',
                'aksi' => 'Barang Masuk',
                'detail' => 'Menambahkan ' . $request->qty_received . ' unit ' . $persediaan->name . ' (No Batch: ' . $batchNumber . ')',
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Barang Masuk sebanyak ' . $request->qty_received . ' unit berhasil dicatat!');
    }

    /**
     * Menampilkan form pengeluaran barang (buat PENGAJUAN dulu, tunggu validasi)
     */
    public function createOut()
    {
        $items = Persediaan::with(['jenisBarang', 'batches'])->get();
        return view('inventory.out', compact('items'));
    }

    /**
     * Simpan PENGAJUAN barang keluar — status "menunggu" dulu sebelum divalidasi
     */
    public function storeOut(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:persediaans,id',
            'qty_out' => 'required|integer|min:1',
        ]);

        $persediaan = Persediaan::findOrFail($request->inventory_item_id);
        $qtyDiminta = (int) $request->qty_out;

        $totalStok = (int) $persediaan->batches()->where('sisa_stok', '>', 0)->sum('sisa_stok');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors([
                'qty_out' => 'Gagal! Stok fisik tidak mencukupi. Total sisa stok saat ini hanya ' . $totalStok . ' unit.'
            ]);
        }

        TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'diajukan_oleh' => Auth::id() ?? 1,
            'jenis' => 'keluar',
            'jumlah' => $qtyDiminta,
            'tanggal' => now()->toDateString(),
            'status' => 'menunggu',
            'unit_kerja_penerima' => $request->unit_kerja_penerima ?? 'Operasional KSOP Banten',
        ]);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Administrator',
            'modul' => 'Persediaan',
            'aksi' => 'Pengajuan Keluar',
            'detail' => 'Mengajukan pengeluaran ' . $qtyDiminta . ' unit ' . $persediaan->name . ' — menunggu persetujuan Validator.',
        ]);

        return redirect()->route('inventory.index')->with('success', 'Pengajuan barang keluar sebanyak ' . $qtyDiminta . ' unit berhasil diajukan! Menunggu persetujuan Validator.');
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
     * Validator menyetujui pengajuan → eksekusi potong stok FIFO
     */
    public function approve($id)
    {
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->findOrFail($id);
        $persediaan = $transaksi->persediaan;
        $qtyDiminta = (int) $transaksi->jumlah;

        $totalStok = (int) $persediaan->batches()->where('sisa_stok', '>', 0)->sum('sisa_stok');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors(['approve' => 'Stok tidak mencukupi! Sisa stok: ' . $totalStok . ' unit. Tidak dapat disetujui.']);
        }

        DB::transaction(function () use ($transaksi, $persediaan, $qtyDiminta) {
            // Potong stok FIFO
            $batches = $persediaan->batches()
                ->where('sisa_stok', '>', 0)
                ->orderBy('tanggal_masuk', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $sisaKebutuhan = $qtyDiminta;
            $rincianPemotongan = [];

            foreach ($batches as $batch) {
                if ($sisaKebutuhan <= 0) break;
                $ambil = min($batch->sisa_stok, $sisaKebutuhan);
                $batch->decrement('sisa_stok', $ambil);
                $sisaKebutuhan -= $ambil;
                $rincianPemotongan[] = $batch->no_batch . " (-{$ambil})";
            }

            // Update status transaksi
            $transaksi->update([
                'status' => 'disetujui',
                'diputuskan_oleh' => Auth::id(),
                'tanggal_keputusan' => now(),
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Validator',
                'modul' => 'Persediaan',
                'aksi' => 'Setujui Keluar',
                'detail' => 'Menyetujui pengeluaran ' . $qtyDiminta . ' unit ' . $persediaan->name . '. FIFO: ' . implode(', ', $rincianPemotongan),
            ]);
        });

        return redirect()->route('inventory.pengajuan')->with('success', 'Pengajuan berhasil disetujui dan stok telah dikurangi secara FIFO!');
    }

    /**
     * Validator menolak pengajuan
     */
    public function reject(Request $request, $id)
    {
        $transaksi = TransaksiPersediaan::where('status', 'menunggu')->findOrFail($id);

        $transaksi->update([
            'status' => 'ditolak',
            'diputuskan_oleh' => Auth::id(),
            'tanggal_keputusan' => now(),
            'catatan_penolakan' => $request->alasan ?? 'Ditolak oleh Validator.',
        ]);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Validator',
            'modul' => 'Persediaan',
            'aksi' => 'Tolak Keluar',
            'detail' => 'Menolak pengajuan pengeluaran ' . $transaksi->jumlah . ' unit ' . ($transaksi->persediaan?->name ?? '#' . $transaksi->persediaan_id) . '. Alasan: ' . ($request->alasan ?? '-'),
        ]);

        return redirect()->route('inventory.pengajuan')->with('success', 'Pengajuan berhasil ditolak.');
    }
}