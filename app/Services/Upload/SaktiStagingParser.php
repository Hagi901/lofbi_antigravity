<?php

namespace App\Services\Upload;

/**
 * Service untuk mem-parsing file SAKTI (seperti bahan_opname_fisik.xlsx) ke dalam baris-baris staging.
 *
 * Mengikuti spesifikasi:
 * - Menggunakan sheet sesuai config (default: "input LOFBI", fallback ke sheet pertama)
 * - Melewati header berulang di tengah halaman laporan
 * - Melewati baris penomoran kolom (1, 2, 3, ...)
 * - Melewati baris kosong atau baris rekapitulasi/total
 * - Menandai baris placeholder (nama / satuan = "-")
 * - Menandai baris mencurigakan (saldo negatif, kode barang tidak valid)
 * - Menjaga kode barang 16 digit utuh sebagai STRING murni beserta pecahan 5-tingkat + nomor urut
 * - Menyimpan harga_satuan = null dan harga_belum_diisi = true
 */
class SaktiStagingParser
{
    /**
     * Parse file SAKTI menjadi array data baris untuk upload_baris.
     *
     * @param string $filePath Path file fisik
     * @param string|null $sheetName Nama sheet opsional
     * @return array Array of associative arrays (siap di-insert ke upload_baris)
     */
    public function parse(string $filePath, ?string $sheetName = null): array
    {
        $config = config('upload_mapping.sakti_opname', []);
        $targetSheet = $sheetName ?? ($config['default_sheet'] ?? 'input LOFBI');

        // Baca data spreadsheet menggunakan XlsxParser
        $rows = XlsxParser::parse($filePath, $targetSheet);

        // Jika sheet yang diminta kosong (misal file hanya punya 1 sheet), coba baca default/sheet1
        if (empty($rows) && $targetSheet !== null) {
            $rows = XlsxParser::parse($filePath, null);
        }

        if (empty($rows)) {
            return [];
        }

        // 1. Deteksi letak header kolom utama
        $headerIndex = -1;
        $colMap = [
            'tgl_perolehan' => -1,
            'kode_barang'   => -1,
            'nama_barang'   => -1,
            'satuan'        => -1,
            'saldo_sistem'  => -1,
            'hasil_opsik'   => -1,
            'kategori'      => -1,
        ];

        foreach ($rows as $idx => $row) {
            if ($this->isHeaderRow($row, $colMap)) {
                $headerIndex = $idx;
                break;
            }
        }

        // Fallback jika tidak ditemukan baris header eksplisit
        if ($headerIndex === -1) {
            $headerIndex = 0;
            $colMap['tgl_perolehan'] = 0;
            $colMap['kode_barang']   = 1;
            $colMap['nama_barang']   = 2;
            $colMap['satuan']        = 3;
            $colMap['saldo_sistem']  = 4;
            $colMap['hasil_opsik']   = 5;
            $colMap['kategori']      = 6;
        }

        $parsedRows = [];
        $nomorUrutBaris = 1;

        // Ambil baris data setelah header pertama
        $rawLines = array_slice($rows, $headerIndex + 1);

        foreach ($rawLines as $row) {
            // A. Cek apakah ini baris header berulang
            $dummyMap = [];
            if ($this->isHeaderRow($row, $dummyMap)) {
                continue;
            }

            // B. Cek apakah ini baris nomor kolom: e.g. ["1", "2", "3", "4", "5", "6", "7"] atau ["(1)", "(2)", ...]
            if ($this->isColumnNumberRow($row)) {
                continue;
            }

            // C. Ekstrak data mentah sesuai mapping kolom
            $rawKode   = $this->getCellString($row, $colMap['kode_barang']);
            $rawNama   = $this->getCellString($row, $colMap['nama_barang']);
            $rawSatuan = $this->getCellString($row, $colMap['satuan']);
            $rawSaldo  = $this->getCellString($row, $colMap['saldo_sistem']);
            $rawOpsik  = $this->getCellString($row, $colMap['hasil_opsik']);
            $rawKat    = $this->getCellString($row, $colMap['kategori']);

            // D. Lewati baris yang sepenuhnya kosong
            if ($rawKode === '' && $rawNama === '' && $rawSatuan === '' && $rawSaldo === '') {
                continue;
            }

            // E. Lewati baris rekapitulasi / jumlah total
            $lowerNama = strtolower($rawNama);
            if (str_starts_with($lowerNama, 'jumlah') || str_starts_with($lowerNama, 'total') || str_starts_with($lowerNama, 'subtotal')) {
                continue;
            }

            // F. Validasi & normalisasi kode barang (16 digit string)
            $kodeData = $this->parseKodeBarang($rawKode);

            // G. Parsing saldo sistem (dukung format angka Indonesia, titik ribuan / koma desimal)
            $saldoSistem = $this->parseInteger($rawSaldo);
            $hasilOpsik  = $rawOpsik !== '' ? $this->parseInteger($rawOpsik) : null;

            // H. Deteksi placeholder & kualitas data
            $isPlaceholder = false;
            $perluPerhatian = false;
            $catatanParser = [];

            // Placeholder: nama atau satuan berupa "-" atau placeholder lain
            $placeholders = ['-', '--', 'n/a', 'none', ''];
            if (in_array(strtolower($rawNama), $placeholders, true) || in_array(strtolower($rawSatuan), $placeholders, true)) {
                $isPlaceholder = true;
                $catatanParser[] = 'Baris placeholder (nama/satuan berisi tanda hubung)';
            }

            // Kode barang tidak sesuai 16 digit
            if (!$kodeData['valid']) {
                $perluPerhatian = true;
                $catatanParser[] = 'Format kode barang tidak baku (bukan 16 digit)';
            }

            // Saldo negatif
            if ($saldoSistem !== null && $saldoSistem < 0) {
                $perluPerhatian = true;
                $catatanParser[] = 'Saldo di sistem bernilai negatif (' . $saldoSistem . ')';
            }

            // Satuan default jika kosong
            $satuan = $rawSatuan !== '' ? strtoupper($rawSatuan) : '-';

            $parsedRows[] = [
                'nomor_baris'          => $nomorUrutBaris++,
                'status'               => 'menunggu',
                'deskripsi_revisi'     => null,
                'kode_barang_lengkap'  => $kodeData['lengkap'],
                'kb_lvl1'              => $kodeData['lvl1'],
                'kb_lvl2'              => $kodeData['lvl2'],
                'kb_lvl3'              => $kodeData['lvl3'],
                'kb_lvl4'              => $kodeData['lvl4'],
                'kb_lvl5'              => $kodeData['lvl5'],
                'kb_seq'               => $kodeData['seq'],
                'nama_barang'          => $rawNama !== '' ? $rawNama : '-',
                'satuan'               => $satuan,
                'saldo_sistem'         => $saldoSistem ?? 0,
                'hasil_opsik'          => $hasilOpsik,
                'kategori_sakti'       => $rawKat !== '' ? $rawKat : null,
                'harga_satuan'         => null,
                'harga_belum_diisi'    => true,
                'no_dokumen'           => null,
                'no_bukti'             => null,
                'tgl_dok'              => null,
                'tgl_buku'             => null,
                'is_placeholder'       => $isPlaceholder,
                'perlu_perhatian'      => $perluPerhatian,
                'catatan_parser'       => !empty($catatanParser) ? implode('; ', $catatanParser) : null,
            ];
        }

        return $parsedRows;
    }

    /**
     * Periksa apakah baris ini merupakan baris header dan petakan indeks kolomnya.
     */
    private function isHeaderRow(array $row, array &$colMap): bool
    {
        $hasNama = false;
        $hasKode = false;
        $tempMap = [
            'tgl_perolehan' => -1,
            'kode_barang'   => -1,
            'nama_barang'   => -1,
            'satuan'        => -1,
            'saldo_sistem'  => -1,
            'hasil_opsik'   => -1,
            'kategori'      => -1,
        ];

        foreach ($row as $idx => $cell) {
            $lower = strtolower(trim((string)$cell));
            if ($lower === '') {
                continue;
            }

            if (str_contains($lower, 'kode barang') || str_contains($lower, 'kode_barang') || $lower === 'kode') {
                $tempMap['kode_barang'] = $idx;
                $hasKode = true;
            } elseif (str_contains($lower, 'nama barang') || str_contains($lower, 'nama_barang') || str_contains($lower, 'uraian barang') || $lower === 'nama') {
                $tempMap['nama_barang'] = $idx;
                $hasNama = true;
            } elseif (str_contains($lower, 'satuan')) {
                $tempMap['satuan'] = $idx;
            } elseif (str_contains($lower, 'saldo') || str_contains($lower, 'sistem') || str_contains($lower, 'stok')) {
                if ($tempMap['saldo_sistem'] === -1) {
                    $tempMap['saldo_sistem'] = $idx;
                }
            } elseif (str_contains($lower, 'opsik') || str_contains($lower, 'fisik')) {
                $tempMap['hasil_opsik'] = $idx;
            } elseif (str_contains($lower, 'kategori')) {
                $tempMap['kategori'] = $idx;
            } elseif (str_contains($lower, 'tanggal') || str_contains($lower, 'tgl') || str_contains($lower, 'perolehan')) {
                $tempMap['tgl_perolehan'] = $idx;
            }
        }

        if ($hasNama && $hasKode) {
            $colMap = $tempMap;
            return true;
        }

        return false;
    }

    /**
     * Deteksi apakah baris ini adalah baris penomoran kolom (misalnya: 1, 2, 3, 4, 5, 6, 7).
     */
    private function isColumnNumberRow(array $row): bool
    {
        $filled = array_filter(array_map('trim', $row), fn($v) => $v !== '');
        if (empty($filled)) {
            return false;
        }

        $allNumbers = true;
        foreach ($filled as $val) {
            $valClean = trim($val, '()[] ');
            if (!is_numeric($valClean) || (int)$valClean > 30) {
                $allNumbers = false;
                break;
            }
        }

        return $allNumbers && count($filled) >= 3;
    }

    /**
     * Parsing dan breakdown kode barang 16 digit.
     * Format:
     * - 1.01.01.08.047.000001 (dengan titik)
     * - 1010108047000001 (tanpa titik, 16 digit)
     */
    public function parseKodeBarang(string $raw): array
    {
        $clean = trim($raw);
        if ($clean === '') {
            return [
                'valid'   => false,
                'lengkap' => null,
                'lvl1'    => null,
                'lvl2'    => null,
                'lvl3'    => null,
                'lvl4'    => null,
                'lvl5'    => null,
                'seq'     => null,
            ];
        }

        // Kasus A: jika kode memiliki titik (e.g. 1.01.01.08.047.000001)
        if (str_contains($clean, '.')) {
            $parts = explode('.', $clean);
            if (count($parts) === 6) {
                $digits = implode('', $parts);
                return [
                    'valid'   => strlen($digits) === 16,
                    'lengkap' => $digits,
                    'lvl1'    => $parts[0],
                    'lvl2'    => $parts[1],
                    'lvl3'    => $parts[2],
                    'lvl4'    => $parts[3],
                    'lvl5'    => $parts[4],
                    'seq'     => $parts[5],
                ];
            }
        }

        // Kasus B: kode tanpa titik, angka murni (misal 16 digit)
        // Hilangkan karakter non-digit jika ada spasi / format lain
        $digits = preg_replace('/[^\d]/', '', $clean);

        if (strlen($digits) === 16) {
            return [
                'valid'   => true,
                'lengkap' => $digits,
                'lvl1'    => substr($digits, 0, 1),   // 1
                'lvl2'    => substr($digits, 1, 2),   // 01
                'lvl3'    => substr($digits, 3, 2),   // 01
                'lvl4'    => substr($digits, 5, 2),   // 08
                'lvl5'    => substr($digits, 7, 3),   // 047
                'seq'     => substr($digits, 10, 6),  // 000001
            ];
        }

        // Jika bukan 16 digit, tetap simpan string aslinya untuk validator periksa
        return [
            'valid'   => false,
            'lengkap' => $clean,
            'lvl1'    => null,
            'lvl2'    => null,
            'lvl3'    => null,
            'lvl4'    => null,
            'lvl5'    => null,
            'seq'     => null,
        ];
    }

    /**
     * Helper string extraction dari sel.
     */
    private function getCellString(array $row, int $colIndex): string
    {
        if ($colIndex < 0 || !isset($row[$colIndex])) {
            return '';
        }

        return trim((string)$row[$colIndex]);
    }

    /**
     * Parsing integer dengan dukungan format Indonesia (1.500 atau 1,500 atau -10).
     */
    private function parseInteger(string $raw): ?int
    {
        $clean = trim($raw);
        if ($clean === '' || $clean === '-') {
            return null;
        }

        $isNegative = str_starts_with($clean, '-') || (str_starts_with($clean, '(') && str_ends_with($clean, ')'));
        // Bersihkan tanda negatif dan pemisah ribuan
        $digitsOnly = preg_replace('/[^\d]/', '', $clean);

        if ($digitsOnly === '') {
            return null;
        }

        $val = (int)$digitsOnly;
        return $isNegative ? -$val : $val;
    }
}
