<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    // Fungsi untuk menampilkan halaman utama Persediaan
    public function index()
    {
        // Menggunakan query aslimu dengan memanggil relasi 'batches'
        $items = InventoryItem::withSum('batches', 'qty_remaining')->get();
        
        return view('inventory', compact('items'));
    }

    // Fungsi untuk menampilkan form Barang Masuk
    public function createIn()
    {
        $items = InventoryItem::all();
        return view('inventory.in', compact('items'));
    }

    // Fungsi untuk menyimpan Barang Masuk
    public function storeIn(Request $request)
    {
        $validatedData = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'qty_received' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validatedData) {
            // 1. Buat Batch Baru
            $batch = InventoryBatch::create([
                'inventory_item_id' => $validatedData['inventory_item_id'],
                'batch_number' => 'BATCH-' . date('YmdHis'), // Generate Batch otomatis
                'qty_received' => $validatedData['qty_received'],
                'qty_remaining' => $validatedData['qty_received'], // Awal masuk, sisa = qty_received
                'purchase_price' => $validatedData['purchase_price'],
            ]);

            // 2. Catat ke Histori Transaksi (Log)
            InventoryTransaction::create([
                'inventory_batch_id' => $batch->id,
                'user_id' => Auth::id() ?? 1, // Mengambil ID user yang sedang login
                'type' => 'in',
                'qty' => $validatedData['qty_received'],
                'notes' => 'Barang masuk ke gudang',
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Barang Masuk berhasil dicatat!');
    }

    // Fungsi untuk menampilkan form Barang Keluar
    public function createOut()
    {
        $items = InventoryItem::all();
        return view('inventory.out', compact('items'));
    }

    // Fungsi untuk memproses Barang Keluar (Algoritma FIFO)
    public function storeOut(Request $request)
    {
        $validatedData = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'qty_out' => 'required|integer|min:1',
        ]);

        $itemId = $validatedData['inventory_item_id'];
        $qtyDiminta = $validatedData['qty_out'];

        // Hitung ketersediaan stok
        $totalStok = InventoryBatch::where('inventory_item_id', $itemId)->sum('qty_remaining');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors(['qty_out' => 'Gagal! Stok tidak mencukupi. Total stok saat ini hanya: ' . $totalStok]);
        }

        DB::transaction(function () use ($itemId, $qtyDiminta) {
            // Urutkan dari barang yang paling lama masuk (created_at asc)
            $batches = InventoryBatch::where('inventory_item_id', $itemId)
                        ->where('qty_remaining', '>', 0)
                        ->orderBy('created_at', 'asc') 
                        ->orderBy('id', 'asc')
                        ->get();

            $sisaKebutuhan = $qtyDiminta;

            foreach ($batches as $batch) {
                if ($sisaKebutuhan <= 0) break;

                // Ambil stok dari batch ini (tergantung mana yang lebih kecil)
                $ambilDariSini = min($batch->qty_remaining, $sisaKebutuhan);

                // Kurangi stok di batch
                $batch->qty_remaining -= $ambilDariSini;
                $batch->save();

                // Catat Log Transaksi Keluar per Batch
                InventoryTransaction::create([
                    'inventory_batch_id' => $batch->id,
                    'user_id' => Auth::id() ?? 1, 
                    'type' => 'out',
                    'qty' => $ambilDariSini,
                    'notes' => 'Barang keluar (Otomatis FIFO)',
                ]);

                // Kurangi sisa kebutuhan yang harus dicarikan di batch berikutnya
                $sisaKebutuhan -= $ambilDariSini;
            }
        });

        return redirect()->route('inventory.index')->with('success', 'Barang Keluar berhasil diproses dengan sistem FIFO!');
    }
}