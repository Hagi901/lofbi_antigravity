<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SimanImportService
{
    /**
     * Import data aset tetap dari file spreadsheet SIMAN (Excel / CSV).
     *
     * @param string $filePath
     * @param int|null $ruanganId
     * @return array
     */
    public function import(string $filePath, ?int $ruanganId = null): array
    {
        $rows = XlsxParser::parse($filePath);

        if (empty($rows)) {
            return [
                'success' => false,
                'message' => 'File kosong atau format tidak dapat dibaca.',
                'imported_count' => 0,
                'errors' => [],
            ];
        }

        // Tentukan ruangan default jika tidak dipilih
        if (!$ruanganId) {
            $ruangan = Ruangan::where('nama', 'like', '%Tata Usaha%')->first() ?? Ruangan::first();
            $ruanganId = $ruangan?->id;
        }

        // 1. Cari baris header
        $headerIndex = -1;
        $colMap = [
            'kode_aset'  => -1,
            'kode_bmn'   => -1,
            'nup'        => -1,
            'nama'       => -1,
            'merk'       => -1,
            'no_seri'    => -1,
            'kategori'   => -1,
            'kondisi'    => -1,
            'nilai'      => -1,
            'tgl'        => -1,
            'manfaat'    => -1,
            'pj'         => -1,
        ];

        foreach ($rows as $idx => $row) {
            $lowerRow = array_map(fn($v) => strtolower(trim((string)$v)), $row);

            $hasNama = false;
            $hasKode = false;

            foreach ($lowerRow as $cIdx => $cell) {
                if (str_contains($cell, 'nama barang') || str_contains($cell, 'nama_barang') || $cell === 'nama' || str_contains($cell, 'uraian barang') || str_contains($cell, 'jenis aset')) {
                    $colMap['nama'] = $cIdx;
                    $hasNama = true;
                }
                if (str_contains($cell, 'kode bmn') || str_contains($cell, 'kode_bmn') || str_contains($cell, 'kodefikasi')) {
                    $colMap['kode_bmn'] = $cIdx;
                    $hasKode = true;
                } elseif (str_contains($cell, 'kode aset') || str_contains($cell, 'kode_aset') || str_contains($cell, 'no aset') || $cell === 'kode') {
                    $colMap['kode_aset'] = $cIdx;
                    $hasKode = true;
                }
                if (str_contains($cell, 'nup') || str_contains($cell, 'no urut') || str_contains($cell, 'no. urut')) {
                    $colMap['nup'] = $cIdx;
                }
                if (str_contains($cell, 'seri') || str_contains($cell, 'serial') || str_contains($cell, 's/n')) {
                    $colMap['no_seri'] = $cIdx;
                }
                if (str_contains($cell, 'merk') || str_contains($cell, 'model') || str_contains($cell, 'tipe')) {
                    $colMap['merk'] = $cIdx;
                }
                if (str_contains($cell, 'kategori') || str_contains($cell, 'kelompok')) {
                    $colMap['kategori'] = $cIdx;
                }
                if (str_contains($cell, 'kondisi') || str_contains($cell, 'keadaan')) {
                    $colMap['kondisi'] = $cIdx;
                }
                if (str_contains($cell, 'nilai') || str_contains($cell, 'harga') || str_contains($cell, 'perolehan')) {
                    if ($colMap['nilai'] === -1) {
                        $colMap['nilai'] = $cIdx;
                    }
                }
                if (str_contains($cell, 'tanggal') || str_contains($cell, 'tgl') || str_contains($cell, 'tahun')) {
                    $colMap['tgl'] = $cIdx;
                }
                if (str_contains($cell, 'manfaat') || str_contains($cell, 'umur') || str_contains($cell, 'masa')) {
                    $colMap['manfaat'] = $cIdx;
                }
                if (str_contains($cell, 'penanggung') || str_contains($cell, 'pj') || str_contains($cell, 'pemegang') || str_contains($cell, 'pemakai')) {
                    $colMap['pj'] = $cIdx;
                }
            }

            if ($hasNama && $hasKode) {
                $headerIndex = $idx;
                break;
            }
        }

        // Fallback default column map
        if ($headerIndex === -1) {
            $headerIndex = 0;
            $colMap['kode_aset'] = 1;
            $colMap['nama'] = 2;
            $colMap['kode_bmn'] = 3;
            $colMap['nup'] = 4;
            $colMap['kondisi'] = 5;
            $colMap['nilai'] = 6;
            $colMap['manfaat'] = 7;
        }

        $importedItems = [];
        $errors = [];
        $dataRows = array_slice($rows, $headerIndex + 1);

        DB::transaction(function () use ($dataRows, $colMap, $ruanganId, &$importedItems, &$errors) {
            $defaultKategori = Kategori::firstOrCreate(
                ['nama' => 'Peralatan & Mesin BMN', 'tipe' => 'aset']
            );

            foreach ($dataRows as $rIdx => $row) {
                $nama = $colMap['nama'] >= 0 && isset($row[$colMap['nama']]) ? trim($row[$colMap['nama']]) : '';
                if ($nama === '' || is_numeric($nama)) {
                    continue;
                }

                if (str_starts_with(strtolower($nama), 'jumlah') || str_starts_with(strtolower($nama), 'total')) {
                    continue;
                }

                $kodeAset = $colMap['kode_aset'] >= 0 && isset($row[$colMap['kode_aset']]) && $row[$colMap['kode_aset']] !== ''
                    ? trim($row[$colMap['kode_aset']])
                    : 'AST-SIMAN-' . str_pad((string)($rIdx + 1), 4, '0', STR_PAD_LEFT);

                $kodeBmn = $colMap['kode_bmn'] >= 0 && isset($row[$colMap['kode_bmn']])
                    ? trim($row[$colMap['kode_bmn']])
                    : null;

                $nup = $colMap['nup'] >= 0 && isset($row[$colMap['nup']])
                    ? (int) preg_replace('/[^\d]/', '', (string)$row[$colMap['nup']])
                    : 1;
                if ($nup <= 0) $nup = 1;

                $merk = $colMap['merk'] >= 0 && isset($row[$colMap['merk']]) ? trim($row[$colMap['merk']]) : $nama;
                $noSeri = $colMap['no_seri'] >= 0 && isset($row[$colMap['no_seri']]) ? trim($row[$colMap['no_seri']]) : null;
                $pj = $colMap['pj'] >= 0 && isset($row[$colMap['pj']]) ? trim($row[$colMap['pj']]) : null;

                $kategoriName = $colMap['kategori'] >= 0 && isset($row[$colMap['kategori']]) && $row[$colMap['kategori']] !== ''
                    ? trim($row[$colMap['kategori']])
                    : 'Peralatan & Mesin BMN';

                // Kondisi mapping
                $kondisiRaw = $colMap['kondisi'] >= 0 && isset($row[$colMap['kondisi']]) ? strtolower(trim($row[$colMap['kondisi']])) : 'baik';
                $kondisi = 'baik';
                if (str_contains($kondisiRaw, 'berat') || $kondisiRaw === 'rb') {
                    $kondisi = 'rusak_berat';
                } elseif (str_contains($kondisiRaw, 'ringan') || $kondisiRaw === 'rr') {
                    $kondisi = 'rusak_ringan';
                }

                // Nilai Perolehan
                $nilaiRaw = $colMap['nilai'] >= 0 && isset($row[$colMap['nilai']]) ? $row[$colMap['nilai']] : '0';
                $nilaiClean = (float) preg_replace('/[^\d.]/', '', str_replace(',', '', (string)$nilaiRaw));

                // Masa Manfaat (Tahun)
                $manfaatRaw = $colMap['manfaat'] >= 0 && isset($row[$colMap['manfaat']]) ? $row[$colMap['manfaat']] : '5';
                $manfaatClean = (int) preg_replace('/[^\d]/', '', (string)$manfaatRaw);
                if ($manfaatClean <= 0) $manfaatClean = 5;

                // Tanggal Perolehan
                $tglRaw = $colMap['tgl'] >= 0 && isset($row[$colMap['tgl']]) ? trim($row[$colMap['tgl']]) : null;
                $tanggalPerolehan = now()->toDateString();
                if ($tglRaw) {
                    try {
                        $tanggalPerolehan = Carbon::parse($tglRaw)->toDateString();
                    } catch (\Exception $e) {
                        if (is_numeric($tglRaw) && strlen($tglRaw) === 4) {
                            $tanggalPerolehan = $tglRaw . '-01-01';
                        }
                    }
                }

                // 1. Kategori
                $kategori = Kategori::firstOrCreate(
                    ['nama' => $kategoriName, 'tipe' => 'aset']
                );

                // 2. Jenis Barang
                $jenisBarang = JenisBarang::firstOrCreate(
                    ['nama_generik' => $nama, 'kategori_id' => $kategori->id]
                );

                // 3. Update or Create Aset
                $aset = Aset::updateOrCreate(
                    ['kode_aset' => $kodeAset],
                    [
                        'jenis_barang_id'   => $jenisBarang->id,
                        'sub_kategori'      => $kategoriName,
                        'kode_bmn'          => $kodeBmn,
                        'nup'               => $nup,
                        'merk'              => $merk,
                        'model'             => '',
                        'no_seri'           => $noSeri,
                        'kondisi'           => $kondisi,
                        'ruangan_id'        => $ruanganId,
                        'penanggung_jawab'  => $pj,
                        'nilai_perolehan'   => $nilaiClean,
                        'tanggal_perolehan' => $tanggalPerolehan,
                        'masa_manfaat'      => $manfaatClean,
                        'metode_penyusutan' => 'Garis Lurus',
                    ]
                );

                $importedItems[] = "{$kodeAset} - {$nama} (NUP: {$nup})";
            }
        });

        return [
            'success'        => true,
            'message'        => 'Sinkronisasi aset SIMAN berhasil! Sebanyak ' . count($importedItems) . ' aset berhasil diperbarui.',
            'imported_count' => count($importedItems),
            'items'          => $importedItems,
            'errors'         => $errors,
        ];
    }
}
