<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Services\ExcelBuilder;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Halaman Pusat Unduh Laporan — kirim daftar kategori ke view
     */
    public function index()
    {
        $kategoris = Kategori::orderBy('nama')->get();
        return view('reports', compact('kategoris'));
    }

    // ── ASET ─────────────────────────────────────────────────────────────────

    /**
     * Export Aset → PDF (browser print / DomPDF jika tersedia)
     */
    public function exportAsetPdf(Request $request)
    {
        $assets = $this->queryAset($request->kategori);
        $kategoriLabel = $request->kategori ?: 'Semua Kategori';
        $filterInfo = ['label' => $kategoriLabel, 'bulan' => $request->bulan, 'tahun' => $request->tahun];

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_aset', compact('assets', 'filterInfo'));
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download('Laporan_Aset_LOFBI_' . date('Ymd') . '.pdf');
        }

        // Fallback: cetak langsung di browser
        return view('reports.pdf_aset', compact('assets', 'filterInfo'));
    }

    /**
     * Export Aset → Excel (.xlsx)
     */
    public function exportAsetExcel(Request $request)
    {
        $assets = $this->queryAset($request->kategori);
        $kategoriLabel = $request->kategori ?: 'Semua Kategori';

        $headings = [
            'No', 'Kode Aset', 'Nama/Jenis Barang', 'Merk', 'Model',
            'Kategori', 'Lokasi/Ruangan', 'Kondisi',
            'Nilai Perolehan (Rp)', 'Akumulasi Penyusutan (Rp)', 'Nilai Buku (Rp)',
            'Tgl Perolehan', 'Masa Manfaat (Thn)',
        ];

        $rows = $assets->values()->map(function ($aset, $index) {
            return [
                $index + 1,
                $aset->kode_aset,
                $aset->name,
                $aset->merk ?? '-',
                $aset->model ?? '-',
                $aset->jenisBarang?->kategori?->nama ?? '-',
                $aset->ruangan?->nama ?? '-',
                ucfirst(str_replace('_', ' ', $aset->kondisi)),
                (float) ($aset->nilai_perolehan ?? 0),
                (float) ($aset->akumulasi_penyusutan ?? 0),
                (float) ($aset->nilai_buku ?? 0),
                $aset->tanggal_perolehan,
                $aset->masa_manfaat,
            ];
        })->toArray();

        $filename = 'Laporan_Aset_LOFBI_' . ($request->kategori ? $request->kategori . '_' : '') . date('Ymd') . '.xlsx';

        return (new ExcelBuilder('Laporan Aset LOFBI'))
            ->addSheet("Aset - {$kategoriLabel}", $headings, $rows)
            ->download($filename);
    }

    // ── PERSEDIAAN ────────────────────────────────────────────────────────────

    /**
     * Export Persediaan → PDF
     */
    public function exportPersediaanPdf(Request $request)
    {
        $items = $this->queryPersediaan($request->kategori);
        $kategoriLabel = $request->kategori ?: 'Semua Kategori';
        $filterInfo = ['label' => $kategoriLabel, 'bulan' => $request->bulan, 'tahun' => $request->tahun];

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_persediaan', compact('items', 'filterInfo'));
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download('Laporan_Persediaan_LOFBI_' . date('Ymd') . '.pdf');
        }

        return view('reports.pdf_persediaan', compact('items', 'filterInfo'));
    }

    /**
     * Export Persediaan → Excel (.xlsx)
     */
    public function exportPersediaanExcel(Request $request)
    {
        $items = $this->queryPersediaan($request->kategori);
        $kategoriLabel = $request->kategori ?: 'Semua Kategori';

        $headings = [
            'No', 'Kode Barang', 'Nama Barang', 'Merk', 'Kategori',
            'Satuan', 'Sisa Stok', 'Stok Minimum', 'Status Stok', 'Jumlah Batch',
        ];

        $rows = $items->values()->map(function ($item, $index) {
            $sisaStok = (int) $item->batches->sum('sisa_stok');
            $minStok = (int) ($item->stok_minimum ?? 0);
            return [
                $index + 1,
                'INV-' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                $item->jenisBarang?->nama_generik ?? '-',
                $item->merk ?? '-',
                $item->jenisBarang?->kategori?->nama ?? '-',
                $item->satuan ?? 'unit',
                $sisaStok,
                $minStok,
                $sisaStok <= 0 ? 'Habis' : ($sisaStok <= $minStok ? 'Menipis' : 'Aman'),
                $item->batches->count(),
            ];
        })->toArray();

        $filename = 'Laporan_Persediaan_LOFBI_' . ($request->kategori ? $request->kategori . '_' : '') . date('Ymd') . '.xlsx';

        return (new ExcelBuilder('Laporan Persediaan LOFBI'))
            ->addSheet("Persediaan - {$kategoriLabel}", $headings, $rows)
            ->download($filename);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────

    private function queryAset(?string $kategori)
    {
        $query = Aset::with(['jenisBarang.kategori', 'ruangan']);

        if ($kategori) {
            $query->whereHas('jenisBarang.kategori', function ($q) use ($kategori) {
                $q->where('nama', 'like', '%' . $kategori . '%');
            });
        }

        return $query->get();
    }

    private function queryPersediaan(?string $kategori)
    {
        $query = Persediaan::with(['jenisBarang.kategori', 'batches']);

        if ($kategori) {
            $query->whereHas('jenisBarang.kategori', function ($q) use ($kategori) {
                $q->where('nama', 'like', '%' . $kategori . '%');
            });
        }

        return $query->get();
    }
}