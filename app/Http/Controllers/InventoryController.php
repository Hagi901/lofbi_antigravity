<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
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
        $items = Persediaan::with(['jenisBarang.kategori', 'batches', 'ruangan'])->get();
        $pengajuanMenunggu = TransaksiPersediaan::where('jenis', 'keluar')
            ->where('status', 'menunggu')
            ->count();

        // Hitung ringkasan statistik
        $totalItems = $items->count();
        $totalStok = $items->sum(fn($i) => $i->batches->sum('sisa_stok'));
        $totalNilaiRupiah = $items->sum(fn($i) => $i->batches->sum(fn($b) => $b->sisa_stok * $b->harga_satuan));

        return view('inventory', compact('items', 'pengajuanMenunggu', 'totalItems', 'totalStok', 'totalNilaiRupiah'));
    }

    /**
     * Form Tambah Master Barang Persediaan Baru
     */
    public function create()
    {
        $categories = Kategori::where('tipe', 'persediaan')->orWhereNull('tipe')->orderBy('nama')->get();
        if ($categories->isEmpty()) {
            $categories = Kategori::orderBy('nama')->get();
        }
        $rooms = Ruangan::orderBy('nama')->get();

        return view('inventory.create', compact('categories', 'rooms'));
    }

    /**
     * Simpan Master Barang Baru (beserta opsi saldo awal / batch pertama)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:kategoris,id',
            'satuan'        => 'required|string|max:50',
            'merk'          => 'nullable|string|max:100',
            'stok_minimum'  => 'nullable|integer|min:0',
            'ruangan_id'    => 'nullable|exists:ruangans,id',
            'initial_qty'   => 'nullable|integer|min:0',
            'initial_price' => 'nullable|numeric|min:0',
            'no_faktur'     => 'nullable|string|max:100',
            'supplier'      => 'nullable|string|max:255',
        ]);

        $persediaan = DB::transaction(function () use ($request) {
            $jenis = JenisBarang::create([
                'nama_generik' => $request->name,
                'kategori_id'  => $request->category_id,
            ]);

            $p = Persediaan::create([
                'jenis_barang_id' => $jenis->id,
                'merk'            => $request->merk,
                'satuan'          => strtoupper($request->satuan),
                'stok_minimum'    => (int) ($request->stok_minimum ?? 0),
                'ruangan_id'      => $request->ruangan_id,
            ]);

            // Jika ada saldo awal
            if ($request->filled('initial_qty') && (int) $request->initial_qty > 0) {
                $qty = (int) $request->initial_qty;
                $harga = (float) ($request->initial_price ?? 0);

                BatchPersediaan::create([
                    'persediaan_id' => $p->id,
                    'no_batch'      => 1,
                    'no_referensi'  => 'REF-' . date('YmdHis'),
                    'no_faktur'     => $request->no_faktur ?: 'SALDO-AWAL-' . date('Y'),
                    'nota_dinas'    => 'ND/' . date('Y/m'),
                    'supplier'      => $request->supplier ?: 'Penyedia Barang KSOP',
                    'tanggal_masuk' => now()->toDateString(),
                    'jumlah_masuk'  => $qty,
                    'harga_satuan'  => $harga,
                    'sisa_stok'     => $qty,
                ]);

                TransaksiPersediaan::create([
                    'persediaan_id'       => $p->id,
                    'diajukan_oleh'       => Auth::id() ?? 1,
                    'diputuskan_oleh'     => Auth::id() ?? 1,
                    'jenis'               => 'masuk',
                    'jumlah'              => $qty,
                    'tanggal'             => now()->toDateString(),
                    'status'              => 'disetujui',
                    'unit_kerja_penerima' => 'Gudang Persediaan KSOP',
                ]);
            }

            AuditLog::create([
                'user_id'   => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul'     => 'Persediaan',
                'aksi'      => 'Tambah Master',
                'detail'    => 'Menambahkan master barang: ' . $request->name . ' (' . strtoupper($request->satuan) . ')',
            ]);

            return $p;
        });

        return redirect()->route('inventory.index')->with('success', 'Master barang ' . $request->name . ' berhasil ditambahkan!');
    }

    /**
     * Tampilkan Kartu Buku Persediaan per Barang
     */
    public function show($id)
    {
        $persediaan = Persediaan::with(['jenisBarang.kategori', 'ruangan', 'batches', 'transaksis.diajukanOleh', 'transaksis.diputuskanOleh'])
            ->findOrFail($id);

        $sisaStok = $persediaan->batches->sum('sisa_stok');
        $totalNilaiRupiah = $persediaan->batches->sum(fn($b) => $b->sisa_stok * $b->harga_satuan);

        return view('inventory.show', compact('persediaan', 'sisaStok', 'totalNilaiRupiah'));
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
            'name'         => 'required|string|max:255',
            'category_id'  => 'required|exists:kategoris,id',
            'satuan'       => 'required|string|max:50',
            'merk'         => 'nullable|string|max:100',
            'stok_minimum' => 'nullable|integer|min:0',
            'ruangan_id'   => 'nullable|exists:ruangans,id',
        ]);

        DB::transaction(function () use ($persediaan, $request) {
            $persediaan->jenisBarang->update([
                'nama_generik' => $request->name,
                'kategori_id'  => $request->category_id,
            ]);

            $persediaan->update([
                'merk'         => $request->merk,
                'satuan'       => strtoupper($request->satuan),
                'stok_minimum' => (int) ($request->stok_minimum ?? 0),
                'ruangan_id'   => $request->ruangan_id,
            ]);

            AuditLog::create([
                'user_id'   => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul'     => 'Persediaan',
                'aksi'      => 'Edit Master',
                'detail'    => 'Memperbarui data master barang ID #' . $persediaan->id . ': ' . $request->name,
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Master barang ' . $request->name . ' berhasil diperbarui!');
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
                'user_id'   => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul'     => 'Persediaan',
                'aksi'      => 'Hapus Master',
                'detail'    => 'Menghapus data persediaan: ' . $namaBarang,
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Barang ' . $namaBarang . ' beserta riwayat batch-nya berhasil dihapus!');
    }

    // ── PENCATATAN MASUK & KELUAR ──────────────────────────────────────────

    /**
     * Menampilkan form pencatatan Barang Masuk (Batch Baru)
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
            'qty_received'      => 'required|integer|min:1',
            'purchase_price'    => 'required|numeric|min:0',
        ]);

        $persediaan = Persediaan::findOrFail($request->inventory_item_id);

        DB::transaction(function () use ($persediaan, $request) {
            $nextBatchNo = ($persediaan->batches()->max('no_batch') ?? 0) + 1;

            BatchPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'no_batch'      => $nextBatchNo,
                'no_referensi'  => $request->no_referensi ?? ('REF-' . date('YmdHis')),
                'no_faktur'     => $request->no_faktur ?? ('FAK-' . date('YmdHis')),
                'nota_dinas'    => $request->nota_dinas ?? ('ND/' . date('Y/m')),
                'supplier'      => $request->supplier ?? 'Penyedia Barang KSOP',
                'tanggal_masuk' => $request->tanggal ?? now()->toDateString(),
                'jumlah_masuk'  => $request->qty_received,
                'harga_satuan'  => $request->purchase_price,
                'sisa_stok'     => $request->qty_received,
            ]);

            TransaksiPersediaan::create([
                'persediaan_id'       => $persediaan->id,
                'diajukan_oleh'       => Auth::id() ?? 1,
                'diputuskan_oleh'     => Auth::id() ?? 1,
                'jenis'               => 'masuk',
                'jumlah'              => $request->qty_received,
                'tanggal'             => $request->tanggal ?? now()->toDateString(),
                'status'              => 'disetujui',
                'unit_kerja_penerima' => 'Gudang Utama KSOP',
            ]);

            AuditLog::create([
                'user_id'   => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Administrator',
                'modul'     => 'Persediaan',
                'aksi'      => 'Barang Masuk',
                'detail'    => 'Menambahkan ' . $request->qty_received . ' unit ' . $persediaan->name . ' (Batch #' . $nextBatchNo . ') @ Rp ' . number_format($request->purchase_price),
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Barang Masuk sebanyak ' . $request->qty_received . ' ' . $persediaan->satuan . ' berhasil dicatat!');
    }

    /**
     * Menampilkan form pengeluaran barang (buat pengajuan)
     */
    public function createOut()
    {
        $items = Persediaan::with(['jenisBarang', 'batches'])->get();
        return view('inventory.out', compact('items'));
    }

    /**
     * Simpan PENGAJUAN barang keluar — status "menunggu" dulu
     */
    public function storeOut(Request $request)
    {
        $request->validate([
            'inventory_item_id'   => 'required|exists:persediaans,id',
            'qty_out'             => 'required|integer|min:1',
            'unit_kerja_penerima' => 'required|string|max:255',
        ]);

        $persediaan = Persediaan::findOrFail($request->inventory_item_id);
        $qtyDiminta = (int) $request->qty_out;

        $totalStok = (int) $persediaan->batches()->where('sisa_stok', '>', 0)->sum('sisa_stok');

        if ($qtyDiminta > $totalStok) {
            return back()->withErrors([
                'qty_out' => 'Gagal! Stok fisik tidak mencukupi. Total sisa stok saat ini hanya ' . $totalStok . ' ' . $persediaan->satuan . '.'
            ]);
        }

        TransaksiPersediaan::create([
            'persediaan_id'       => $persediaan->id,
            'diajukan_oleh'       => Auth::id() ?? 1,
            'jenis'               => 'keluar',
            'jumlah'              => $qtyDiminta,
            'tanggal'             => $request->tanggal ?? now()->toDateString(),
            'status'              => 'menunggu',
            'unit_kerja_penerima' => $request->unit_kerja_penerima,
        ]);

        AuditLog::create([
            'user_id'   => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Administrator',
            'modul'     => 'Persediaan',
            'aksi'      => 'Pengajuan Keluar',
            'detail'    => 'Mengajukan pengeluaran ' . $qtyDiminta . ' ' . $persediaan->satuan . ' ' . $persediaan->name . ' untuk ' . $request->unit_kerja_penerima . ' — menunggu persetujuan Validator.',
        ]);

        return redirect()->route('inventory.index')->with('success', 'Pengajuan barang keluar sebanyak ' . $qtyDiminta . ' ' . $persediaan->satuan . ' berhasil diajukan! Menunggu persetujuan Validator.');
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
            return back()->withErrors(['approve' => 'Stok tidak mencukupi! Sisa stok: ' . $totalStok . ' ' . $persediaan->satuan . '. Tidak dapat disetujui.']);
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
                $rincianPemotongan[] = "Batch #" . $batch->no_batch . " (-{$ambil})";
            }

            // Update status transaksi
            $transaksi->update([
                'status'            => 'disetujui',
                'diputuskan_oleh'   => Auth::id(),
                'tanggal_keputusan' => now(),
            ]);

            AuditLog::create([
                'user_id'   => Auth::id() ?? 1,
                'user_name' => Auth::user()->name ?? 'Validator',
                'modul'     => 'Persediaan',
                'aksi'      => 'Setujui Keluar',
                'detail'    => 'Menyetujui pengeluaran ' . $qtyDiminta . ' ' . $persediaan->satuan . ' ' . $persediaan->name . '. FIFO: ' . implode(', ', $rincianPemotongan),
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
            'status'            => 'ditolak',
            'diputuskan_oleh'   => Auth::id(),
            'tanggal_keputusan' => now(),
            'catatan_penolakan' => $request->alasan ?? 'Ditolak oleh Validator.',
        ]);

        AuditLog::create([
            'user_id'   => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Validator',
            'modul'     => 'Persediaan',
            'aksi'      => 'Tolak Keluar',
            'detail'    => 'Menolak pengajuan pengeluaran ' . $transaksi->jumlah . ' unit ' . ($transaksi->persediaan?->name ?? '#' . $transaksi->persediaan_id) . '. Alasan: ' . ($request->alasan ?? '-'),
        ]);

        return redirect()->route('inventory.pengajuan')->with('success', 'Pengajuan berhasil ditolak.');
    }
}