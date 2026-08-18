<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\Asset; // Panggil model Asset
use Barryvdh\DomPDF\Facade\Pdf; // Panggil library PDF

class ReportController extends Controller
{
    // Halaman Pusat Unduh
    public function index()
    {
        return view('download'); 
    }

    // Fungsi Export PDF Laporan Aset
    public function exportAsetPdf()
    {
        // Ambil data aset beserta relasinya
        $assets = Asset::with(['category', 'subCategory', 'room'])->get();

        // Load tampilan khusus PDF (kita buat filenya setelah ini)
        $pdf = Pdf::loadView('reports.pdf_aset', compact('assets'));

        // Atur ukuran kertas menjadi A4 Landscape (Mendatar) agar tabelnya muat
        $pdf->setPaper('A4', 'landscape');

        // Download filenya dengan nama ini
        return $pdf->download('Laporan_Aset_LOFBI_'.date('Ymd').'.pdf');
    }
}