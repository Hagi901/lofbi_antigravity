<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Ruangan;
use App\Services\ExcelBuilder;
use App\Services\SaktiImportService;
use App\Services\SimanImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    /**
     * Halaman Utama Pusat Sinkronisasi SIMAN & SAKTI
     */
    public function index()
    {
        $rooms = Ruangan::orderBy('nama')->get();
        $recentImports = AuditLog::where('modul', 'Import Data')
            ->latest()
            ->take(10)
            ->get();

        return view('import.index', compact('rooms', 'recentImports'));
    }

    /**
     * Import Laporan Persediaan SAKTI
     */
    public function importSakti(Request $request, SaktiImportService $service)
    {
        $request->validate([
            'file_sakti' => 'required|file|max:20480', // Maks 20 MB
            'ruangan_id' => 'nullable|exists:ruangans,id',
        ]);

        $file = $request->file('file_sakti');
        $path = $file->getRealPath();

        try {
            $result = $service->import($path, $request->ruangan_id);

            if ($result['success']) {
                AuditLog::create([
                    'user_id'   => Auth::id() ?? 1,
                    'user_name' => Auth::user()->name ?? 'Administrator',
                    'modul'     => 'Import Data',
                    'aksi'      => 'Import SAKTI',
                    'detail'    => 'Import persediaan SAKTI (' . $file->getClientOriginalName() . ') — ' . $result['imported_count'] . ' item berhasil disinkronkan',
                ]);

                return redirect()->route('import.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('import.index')
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('import.index')
                ->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Import Laporan Aset Tetap SIMAN
     */
    public function importSiman(Request $request, SimanImportService $service)
    {
        $request->validate([
            'file_siman' => 'required|file|max:20480', // Maks 20 MB
            'ruangan_id' => 'nullable|exists:ruangans,id',
        ]);

        $file = $request->file('file_siman');
        $path = $file->getRealPath();

        try {
            $result = $service->import($path, $request->ruangan_id);

            if ($result['success']) {
                AuditLog::create([
                    'user_id'   => Auth::id() ?? 1,
                    'user_name' => Auth::user()->name ?? 'Administrator',
                    'modul'     => 'Import Data',
                    'aksi'      => 'Import SIMAN',
                    'detail'    => 'Import aset SIMAN (' . $file->getClientOriginalName() . ') — ' . $result['imported_count'] . ' aset berhasil disinkronkan',
                ]);

                return redirect()->route('import.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('import.index')
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('import.index')
                ->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Download template spreadsheet standar
     */
    public function downloadTemplate(string $type)
    {
        if ($type === 'sakti') {
            $headings = ['No', 'Kode Barang', 'Nama Barang', 'Kategori', 'Satuan', 'Saldo Stok', 'Harga Satuan (Rp)', 'Stok Minimum', 'Merk'];
            $rows = [
                [1, '1.01.01.04.001.000013', 'BBM KUPON KAPAL', 'Bahan Bakar', 'LITER', 1200, 12300, 200, 'Pertamina Dex'],
                [2, '1.01.03.01.014.000037', 'BUKU PELAUT HUBPOST', 'Dokumen Pelaut', 'BUAH', 1500, 66156, 100, 'Standar Hubla'],
                [3, '1.01.01.08.047.000001', 'AIR MINERAL GALON', 'Rumah Tangga', 'GALON', 84, 50000, 10, 'Aqua'],
                [4, '1.01.03.02.001.000001', 'KERTAS HVS A4 80 GRAM', 'ATK', 'RIM', 50, 59000, 10, 'Sinar Dunia'],
                [5, '1.01.03.13.002.000001', 'OLI MESTRANIA 2T', 'Pelumas & Oli', 'LITER', 77, 58700, 15, 'Pertamina'],
            ];

            return (new ExcelBuilder('Template Import Persediaan SAKTI'))
                ->addSheet('Template SAKTI', $headings, $rows)
                ->download('Template_Import_Persediaan_SAKTI.xlsx');
        }

        // SIMAN Template
        $headings = [
            'No', 'Kode Aset', 'Kodefikasi BMN (10 Digit)', 'NUP', 'Nama Barang / Tipe',
            'Merk / Model', 'Nomor Seri Pabrik (S/N)', 'Kategori', 'Kondisi (Baik/RR/RB)',
            'Nilai Perolehan (Rp)', 'Tgl Perolehan (YYYY-MM-DD)', 'Masa Manfaat (Tahun)', 'Penanggung Jawab',
        ];
        $rows = [
            [1, 'AST-2026-001', '3.05.01.05.001', 1, 'Laptop ASUS ExpertBook B9', 'ASUS B9450', 'SN-88392019-ASUS', 'Elektronik & IT', 'Baik', 15000000, '2024-01-15', 5, 'Ahmad Fauzi / NIP. 1990...'],
            [2, 'AST-2026-002', '3.05.02.01.002', 1, 'Printer Canon PIXMA G2020', 'Canon G2020', 'SN-CN-2023-098', 'Elektronik & IT', 'Baik', 2500000, '2024-03-10', 5, 'Seksi Kepegawaian'],
            [3, 'AST-2026-003', '3.05.01.04.001', 1, 'PC All-in-One HP 24-df', 'HP 24-df0014d', 'SN-HP-998231', 'Elektronik & IT', 'Rusak Ringan', 9500000, '2023-06-20', 4, 'Ruang TU'],
        ];

        return (new ExcelBuilder('Template Import Aset SIMAN'))
            ->addSheet('Template SIMAN', $headings, $rows)
            ->download('Template_Import_Aset_SIMAN.xlsx');
    }
}
