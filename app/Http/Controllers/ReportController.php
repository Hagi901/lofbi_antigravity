<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\BatchPersediaan;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Halaman Pusat Unduh Laporan
     */
    public function index()
    {
        return view('download'); 
    }

    /**
     * Ekspor Laporan Aset (PDF / Cetak)
     */
    public function exportAsetPdf()
    {
        $assets = Aset::with(['jenisBarang.kategori', 'ruangan'])->get();

        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_aset', compact('assets'));
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download('Laporan_Aset_LOFBI_' . date('Ymd') . '.pdf');
        }

        return view('reports.pdf_aset', compact('assets'));
    }
}