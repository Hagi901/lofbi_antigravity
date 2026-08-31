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
        return view('inventory', compact('items'));
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
            
            // 1. Buat Batch Baru
            $batch = BatchPersediaan::create([
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

            // 2. Catat Log Transaksi Masuk
            TransaksiPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'user_id' => Auth::id() ?? 1,
                'jenis' => 'masuk',
                'jumlah' => $request->qty_received,
                'harga_satuan' => $request->purchase_price,
                'tanggal' => now()->toDateString(),
                'status' => 'disetujui',
                'no_referensi' => 'IN-' . date('YmdHis'),
                'keterangan' => 'Barang Masuk ke ' . $batchNumber,
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'BARANG_MASUK',
                'table_name' => 'batch_persediaans',
                'record_id' => $batch->id,
                'old_values' => null,
                'new_values' => $batch->toArray(),
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Barang Masuk sebanyak ' . $request->qty_received . ' unit berhasil dicatat!');
    }

    /**
     * Menampilkan form pengeluaran barang
     */
    public function createOut()
    {
        $items = Persediaan::with(['jenisBarang', 'batches'])->get();
        return view('inventory.out', compact('items'));
    }

    /**
     * Memproses pengeluaran barang dengan algoritma FIFO murni
     */
    public function storeOut(Request $request)
    {
        $request->validate([
            'inventory_item_id' => 'required|exists:persediaans,id',
            'qty_out' => 'required|integer|min:1',
        ]);

        $persediaan = Persediaan::findOrFail($request->inventory_item_id);
        $qtyDiminta = (int) $request->qty_out;

        // Hitung total ketersediaan stok
        $totalStok = (int) $persediaan->batches()->where('sisa_stok', '>', 0)->sum('sisa_stok');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors([
                'qty_out' => 'Gagal! Stok fisik tidak mencukupi. Total sisa stok saat ini hanya ' . $totalStok . ' unit.'
            ]);
        }

        DB::transaction(function () use ($persediaan, $qtyDiminta, $request) {
            // Urutkan batch dari yang paling awal masuk (FIFO)
            $batches = $persediaan->batches()
                ->where('sisa_stok', '>', 0)
                ->orderBy('tanggal_masuk', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $sisaKebutuhan = $qtyDiminta;
            $rincianPemotongan = [];

            foreach ($batches as $batch) {
                if ($sisaKebutuhan <= 0) {
                    break;
                }

                $ambil = min($batch->sisa_stok, $sisaKebutuhan);
                $batch->decrement('sisa_stok', $ambil);
                $sisaKebutuhan -= $ambil;

                $rincianPemotongan[] = $batch->no_batch . " (-{$ambil})";
            }

            // Catat Transaksi Pengeluaran
            TransaksiPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'user_id' => Auth::id() ?? 1,
                'jenis' => 'keluar',
                'jumlah' => $qtyDiminta,
                'harga_satuan' => $batches->first()?->harga_satuan ?? 0,
                'tanggal' => now()->toDateString(),
                'status' => 'disetujui',
                'no_referensi' => 'OUT-' . date('YmdHis'),
                'keterangan' => 'Pemotongan FIFO: ' . implode(', ', $rincianPemotongan),
                'unit_kerja_penerima' => $request->unit_kerja_penerima ?? 'Operasional KSOP',
            ]);

            AuditLog::create([
                'user_id' => Auth::id() ?? 1,
                'action' => 'BARANG_KELUAR_FIFO',
                'table_name' => 'persediaans',
                'record_id' => $persediaan->id,
                'old_values' => ['stok_sebelumnya' => $totalStok],
                'new_values' => ['qty_keluar' => $qtyDiminta, 'rincian_batch' => $rincianPemotongan],
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Pengeluaran ' . $qtyDiminta . ' unit barang berhasil diproses dengan metode FIFO!');
    }
}