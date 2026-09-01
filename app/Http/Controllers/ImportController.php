<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Ruangan;
use App\Services\ExcelBuilder;
use App\Services\SaktiImportService;
use App\Services\SimanImportService;
use App\Services\XlsxParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    /**
     * Halaman Utama Pusat Sinkronisasi SIMAN & SAKTI (Single Smart Portal)
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
     * Smart Auto-Detect Import (Satu Pintu Upload Cerdas)
     */
    public function importAuto(Request $request, SaktiImportService $saktiService, SimanImportService $simanService)
    {
        $file = $request->file('file_dokumen') ?? $request->file('file_sakti') ?? $request->file('file_siman');
        
        if (!$file) {
            return redirect()->route('import.index')->with('error', 'File laporan spreadsheet wajib diunggah.');
        }

        $request->validate([
            'jenis_dokumen'=> 'nullable|string|in:auto,sakti,siman',
            'ruangan_id'   => 'nullable|exists:ruangans,id',
        ]);

        $path = $file->getRealPath();
        $fileName = $file->getClientOriginalName();
        $targetType = $request->jenis_dokumen ?: 'auto';

        try {
            // 1. Jika mode 'auto', jalankan deteksi tipe dokumen berdasarkan konten
            if ($targetType === 'auto') {
                $targetType = $this->detectDocumentType($path, $fileName);
            }

            // 2. Eksekusi import sesuai tipe yang terdeteksi
            if ($targetType === 'sakti') {
                $result = $saktiService->import($path, $request->ruangan_id);
                $docLabel = 'Persediaan SAKTI';
                $icon = 'fa-boxes-stacked';
            } else {
                $result = $simanService->import($path, $request->ruangan_id);
                $docLabel = 'Aset Tetap SIMAN';
                $icon = 'fa-landmark';
            }

            if ($result['success']) {
                AuditLog::create([
                    'user_id'   => Auth::id() ?? 1,
                    'user_name' => Auth::user()->name ?? 'Administrator',
                    'modul'     => 'Import Data',
                    'aksi'      => 'Import ' . ($targetType === 'sakti' ? 'SAKTI' : 'SIMAN'),
                    'detail'    => 'Sinkronisasi cerdas ' . $docLabel . ' (' . $fileName . ') — ' . $result['imported_count'] . ' data berhasil diperbarui',
                ]);

                $msg = '✨ [Auto-Detector] Terdeteksi sebagai dokumen ' . $docLabel . '! ' . $result['message'];

                return redirect()->route('import.index')->with('success', $msg);
            } else {
                return redirect()->route('import.index')->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('import.index')
                ->with('error', 'Terjadi kendala saat membaca file: ' . $e->getMessage());
        }
    }

    /**
     * Algoritma Deteksi Otomatis Jenis Dokumen (SIMAN vs SAKTI)
     */
    private function detectDocumentType(string $path, string $fileName): string
    {
        $lowerName = strtolower($fileName);
        if (str_contains($lowerName, 'sakti') || str_contains($lowerName, 'persediaan')) {
            return 'sakti';
        }
        if (str_contains($lowerName, 'siman') || str_contains($lowerName, 'aset')) {
            return 'siman';
        }

        // Baca 20 baris pertama untuk identifikasi pola konten
        $rows = XlsxParser::parse($path);
        $sampleText = '';
        foreach (array_slice($rows, 0, 20) as $row) {
            $sampleText .= ' ' . implode(' ', array_map(fn($v) => strtolower(trim((string)$v)), $row));
        }

        // Bobot skor kecocokan
        $saktiScore = 0;
        $simanScore = 0;

        // Indikator SAKTI (Persediaan)
        if (str_contains($sampleText, 'persediaan')) $saktiScore += 3;
        if (str_contains($sampleText, 'satuan')) $saktiScore += 2;
        if (str_contains($sampleText, 'saldo')) $saktiScore += 2;
        if (str_contains($sampleText, 'kuantitas')) $saktiScore += 2;
        if (str_contains($sampleText, 'rincian buku persediaan')) $saktiScore += 5;
        if (preg_match('/\b1\.\d{2}\.\d{2}\b/', $sampleText)) $saktiScore += 3; // Kode akun persediaan (1.01...)

        // Indikator SIMAN (Aset Tetap)
        if (str_contains($sampleText, 'nup')) $simanScore += 4;
        if (str_contains($sampleText, 'masa manfaat') || str_contains($sampleText, 'umur ekonomis')) $simanScore += 3;
        if (str_contains($sampleText, 'nilai perolehan') || str_contains($sampleText, 'akumulasi')) $simanScore += 3;
        if (str_contains($sampleText, 'no seri') || str_contains($sampleText, 'serial number')) $simanScore += 2;
        if (str_contains($sampleText, 'penanggung jawab') || str_contains($sampleText, 'pemakai')) $simanScore += 2;
        if (preg_match('/\b3\.\d{2}\.\d{2}\b/', $sampleText)) $simanScore += 3; // Kodefikasi BMN aset (3.05...)

        return $simanScore > $saktiScore ? 'siman' : 'sakti';
    }

    /**
     * Endpoint legacy import langsung SAKTI (tetap didukung)
     */
    public function importSakti(Request $request, SaktiImportService $service)
    {
        if ($request->hasFile('file_sakti') && !$request->hasFile('file_dokumen')) {
            $request->files->set('file_dokumen', $request->file('file_sakti'));
        }
        $request->merge(['jenis_dokumen' => 'sakti']);
        return $this->importAuto($request, $service, app(SimanImportService::class));
    }

    /**
     * Endpoint legacy import langsung SIMAN (tetap didukung)
     */
    public function importSiman(Request $request, SimanImportService $service)
    {
        if ($request->hasFile('file_siman') && !$request->hasFile('file_dokumen')) {
            $request->files->set('file_dokumen', $request->file('file_siman'));
        }
        $request->merge(['jenis_dokumen' => 'siman']);
        return $this->importAuto($request, app(SaktiImportService::class), $service);
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
