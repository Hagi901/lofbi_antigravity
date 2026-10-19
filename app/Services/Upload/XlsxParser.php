<?php

namespace App\Services\Upload;

use SimpleXMLElement;
use ZipArchive;

/**
 * XlsxParser - Parser file .xlsx & .csv murni berbasis PHP native (ZipArchive & SimpleXMLElement).
 * Tanpa perlu library eksternal berat (phpoffice/phpspreadsheet).
 */
class XlsxParser
{
    /**
     * Membaca file spreadsheet (.xlsx atau .csv) dan mengembalikan array of rows.
     *
     * @param  string  $filePath  Path absolut ke file
     * @param  string|null  $sheetName  Nama sheet spesifik yang ingin dibaca (untuk .xlsx)
     * @return array Array berisi baris-baris data (array of arrays)
     */
    public static function parse(string $filePath, ?string $sheetName = null): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            return self::parseCsv($filePath);
        }

        if ($ext === 'xlsx') {
            return self::parseXlsx($filePath, $sheetName);
        }

        // Fallback coba baca sebagai CSV
        return self::parseCsv($filePath);
    }

    /**
     * Parse file CSV dengan deteksi otomatis delimiter (, atau ; atau tab).
     */
    private static function parseCsv(string $filePath): array
    {
        $rows = [];
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return $rows;
        }

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return $rows;
        }

        // Deteksi delimiter dari baris pertama
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            // Trim setiap cell
            $cleaned = array_map(fn ($v) => trim((string) $v), $data);
            // Lewati baris yang sepenuhnya kosong
            if (! empty(array_filter($cleaned, fn ($v) => $v !== ''))) {
                $rows[] = $cleaned;
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Dapatkan daftar nama sheet dari file .xlsx
     */
    public static function getSheetNames(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            return [];
        }

        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $names = [];
        $wbXml = $zip->getFromName('xl/workbook.xml');
        if ($wbXml !== false) {
            $xml = simplexml_load_string($wbXml);
            if ($xml && isset($xml->sheets->sheet)) {
                foreach ($xml->sheets->sheet as $sh) {
                    $names[] = (string) $sh['name'];
                }
            }
        }
        $zip->close();

        return $names;
    }

    /**
     * Parse file .xlsx OpenXML Spreadsheet.
     */
    private static function parseXlsx(string $filePath, ?string $sheetName = null): array
    {
        $rows = [];
        $zip = new ZipArchive;

        if ($zip->open($filePath) !== true) {
            return $rows;
        }

        // 1. Ekstrak sharedStrings.xml jika ada
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Tentukan target worksheet berdasarkan nama sheet atau default
        $sheetPath = self::resolveWorksheetPath($zip, $sheetName);
        $sheetXml = $zip->getFromName($sheetPath);

        // Fallback jika tidak ditemukan
        if ($sheetXml === false && $sheetPath !== 'xl/worksheets/sheet1.xml') {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        }

        if ($sheetXml !== false) {
            $xml = simplexml_load_string($sheetXml);
            if ($xml && isset($xml->sheetData->row)) {
                foreach ($xml->sheetData->row as $row) {
                    $rowData = [];
                    $currentIndex = 0;

                    foreach ($row->c as $cell) {
                        $cellRef = (string) $cell['r'];
                        // Ambil kolom (huruf) dari cell reference (e.g. A1, B1, AA1)
                        preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches);
                        $colLetter = $matches[1] ?? 'A';
                        $targetIndex = self::columnLetterToIndex($colLetter);

                        // Isi kolom kosong di antara cell jika ada
                        while ($currentIndex < $targetIndex) {
                            $rowData[] = '';
                            $currentIndex++;
                        }

                        $type = (string) $cell['t'];
                        $val = isset($cell->v) ? (string) $cell->v : '';

                        if ($type === 's') {
                            // Shared string
                            $strIndex = (int) $val;
                            $cellValue = $sharedStrings[$strIndex] ?? '';
                        } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                            $cellValue = (string) $cell->is->t;
                        } else {
                            $cellValue = $val;
                        }

                        $rowData[] = trim($cellValue);
                        $currentIndex++;
                    }

                    if (! empty(array_filter($rowData, fn ($v) => $v !== ''))) {
                        $rows[] = $rowData;
                    }
                }
            }
        }

        $zip->close();

        return $rows;
    }

    /**
     * Resolusi path file XML worksheet dari nama sheet pada xl/workbook.xml
     */
    private static function resolveWorksheetPath(ZipArchive $zip, ?string $sheetName = null): string
    {
        $defaultPath = 'xl/worksheets/sheet1.xml';
        $wbXml = $zip->getFromName('xl/workbook.xml');
        if ($wbXml === false) {
            return $defaultPath;
        }

        $xml = simplexml_load_string($wbXml);
        if (! $xml || ! isset($xml->sheets->sheet)) {
            return $defaultPath;
        }

        $rId = null;
        $firstRId = null;

        foreach ($xml->sheets->sheet as $sheet) {
            $name = (string) $sheet['name'];
            $relAttrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $currentRId = (string) ($relAttrs['id'] ?? $sheet['id'] ?? '');

            if ($firstRId === null) {
                $firstRId = $currentRId;
            }

            if ($sheetName !== null && strcasecmp(trim($name), trim($sheetName)) === 0) {
                $rId = $currentRId;
                break;
            }
        }

        if (! $rId && $sheetName !== null) {
            // Cek kecocokan parsial
            foreach ($xml->sheets->sheet as $sheet) {
                $name = (string) $sheet['name'];
                if (str_contains(strtolower($name), strtolower(trim($sheetName)))) {
                    $relAttrs = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
                    $rId = (string) ($relAttrs['id'] ?? $sheet['id'] ?? '');
                    break;
                }
            }
        }

        if (! $rId) {
            $rId = $firstRId;
        }

        if (! $rId) {
            return $defaultPath;
        }

        // Cari relasi di xl/_rels/workbook.xml.rels
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($relsXml !== false) {
            $rXml = simplexml_load_string($relsXml);
            if ($rXml && isset($rXml->Relationship)) {
                foreach ($rXml->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rId) {
                        $target = (string) $rel['Target'];
                        $cleanTarget = ltrim($target, '/');
                        if (! str_starts_with($cleanTarget, 'xl/')) {
                            return 'xl/'.$cleanTarget;
                        }

                        return $cleanTarget;
                    }
                }
            }
        }

        return $defaultPath;
    }

    /**
     * Konversi huruf kolom Excel (A, B, ..., Z, AA, AB) ke index 0-based.
     */
    private static function columnLetterToIndex(string $letter): int
    {
        $letter = strtoupper($letter);
        $len = strlen($letter);
        $index = 0;

        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }
}
