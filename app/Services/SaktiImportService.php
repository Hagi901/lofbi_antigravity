<?php

namespace App\Services;

use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\Ruangan;
use Illuminate\Support\Facades\DB;

class SaktiImportService
{
    /**
     * Import data persediaan dari file spreadsheet (Excel / CSV).
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

        // Tentukan ruangan default jika tidak dispesifikasikan
        if (!$ruanganId) {
            $ruangan = Ruangan::where('nama', 'like', '%Gudang%')->first() ?? Ruangan::first();
            $ruanganId = $ruangan?->id;
        }

        // 1. Cari baris header
        $headerIndex = -1;
        $colMap = [
            'kode'     => -1,
            'nama'     => -1,
            'kategori' => -1,
            'satuan'   => -1,
            'stok'     => -1,
            'harga'    => -1,
            'min_stok' => -1,
            'merk'     => -1,
        ];

        foreach ($rows as $idx => $row) {
            $lowerRow = array_map(fn($v) => strtolower(trim((string)$v)), $row);
            
            // Cek apakah baris ini memuat kata kunci header persediaan
            $hasNama = false;
            $hasKodeOrSatuan = false;

            foreach ($lowerRow as $cIdx => $cell) {
                if (str_contains($cell, 'nama barang') || str_contains($cell, 'nama_barang') || $cell === 'nama' || str_contains($cell, 'uraian barang') || str_contains($cell, 'jenis barang')) {
                    $colMap['nama'] = $cIdx;
                    $hasNama = true;
                }
                if (str_contains($cell, 'kode') || str_contains($cell, 'kode barang') || str_contains($cell, 'kode_barang')) {
                    $colMap['kode'] = $cIdx;
                    $hasKodeOrSatuan = true;
                }
                if ($cell === 'satuan' || (str_contains($cell, 'satuan') && !str_contains($cell, 'harga') && !str_contains($cell, 'nilai'))) {
                    $colMap['satuan'] = $cIdx;
                    $hasKodeOrSatuan = true;
                }
                if (str_contains($cell, 'saldo') || str_contains($cell, 'stok') || str_contains($cell, 'kuantitas') || str_contains($cell, 'unit') || str_contains($cell, 'jumlah')) {
                    if ($colMap['stok'] === -1 && !str_contains($cell, 'harga') && !str_contains($cell, 'minimum')) {
                        $colMap['stok'] = $cIdx;
                    }
                }
                if (str_contains($cell, 'harga') || str_contains($cell, 'tarif') || str_contains($cell, 'nilai')) {
                    if ($colMap['harga'] === -1) {
                        $colMap['harga'] = $cIdx;
                    }
                }
                if (str_contains($cell, 'kategori') || str_contains($cell, 'kelompok')) {
                    $colMap['kategori'] = $cIdx;
                }
                if (str_contains($cell, 'merk') || str_contains($cell, 'spesifikasi')) {
                    $colMap['merk'] = $cIdx;
                }
                if (str_contains($cell, 'minimum') || str_contains($cell, 'min')) {
                    $colMap['min_stok'] = $cIdx;
                }
            }

            if ($hasNama && $hasKodeOrSatuan) {
                $headerIndex = $idx;
                break;
            }
        }

        // Jika tidak ada header spesifik, gunakan asumsi kolom default:
        // Col 0: No, Col 1: Kode, Col 2: Nama, Col 3: Kategori, Col 4: Satuan, Col 5: Stok, Col 6: Harga
        if ($headerIndex === -1) {
            $headerIndex = 0; // Mulai dari baris 1 sebagai data jika tidak ada header
            $colMap['kode'] = 1;
            $colMap['nama'] = 2;
            $colMap['kategori'] = 3;
            $colMap['satuan'] = 4;
            $colMap['stok'] = 5;
            $colMap['harga'] = 6;
        }

        $importedItems = [];
        $errors = [];
        $dataRows = array_slice($rows, $headerIndex + 1);

        DB::transaction(function () use ($dataRows, $colMap, $ruanganId, &$importedItems, &$errors) {
            $defaultKategori = Kategori::firstOrCreate(
                ['nama' => 'Persediaan BMN', 'tipe' => 'persediaan']
            );

            foreach ($dataRows as $rIdx => $row) {
                $nama = $colMap['nama'] >= 0 && isset($row[$colMap['nama']]) ? trim($row[$colMap['nama']]) : '';
                if ($nama === '' || is_numeric($nama)) {
                    continue; // Lewati baris kosong atau baris nomor
                }

                // Lewati baris rekap / jumlah total jika ada
                if (str_starts_with(strtolower($nama), 'jumlah') || str_starts_with(strtolower($nama), 'total')) {
                    continue;
                }

                $satuan = $colMap['satuan'] >= 0 && isset($row[$colMap['satuan']]) && $row[$colMap['satuan']] !== ''
                    ? strtoupper(trim($row[$colMap['satuan']]))
                    : 'Unit';

                $kategoriName = $colMap['kategori'] >= 0 && isset($row[$colMap['kategori']]) && $row[$colMap['kategori']] !== ''
                    ? trim($row[$colMap['kategori']])
                    : 'Persediaan BMN';

                $merk = $colMap['merk'] >= 0 && isset($row[$colMap['merk']]) ? trim($row[$colMap['merk']]) : null;

                // Parsing angka stok
                $stokRaw = $colMap['stok'] >= 0 && isset($row[$colMap['stok']]) ? $row[$colMap['stok']] : '0';
                $stokClean = (int) preg_replace('/[^\d]/', '', str_replace(',', '', (string)$stokRaw));

                // Parsing angka harga satuan
                $hargaRaw = $colMap['harga'] >= 0 && isset($row[$colMap['harga']]) ? $row[$colMap['harga']] : '0';
                $hargaClean = (float) preg_replace('/[^\d.]/', '', str_replace(',', '', (string)$hargaRaw));

                $minStok = $colMap['min_stok'] >= 0 && isset($row[$colMap['min_stok']])
                    ? (int) preg_replace('/[^\d]/', '', (string)$row[$colMap['min_stok']])
                    : 5;

                // 1. Kategori
                $kategori = Kategori::firstOrCreate(
                    ['nama' => $kategoriName, 'tipe' => 'persediaan']
                );

                // 2. Jenis Barang
                $jenisBarang = JenisBarang::firstOrCreate(
                    ['nama_generik' => $nama, 'kategori_id' => $kategori->id]
                );

                // 3. Persediaan Master
                $persediaan = Persediaan::firstOrCreate(
                    ['jenis_barang_id' => $jenisBarang->id, 'merk' => $merk],
                    [
                        'satuan' => $satuan,
                        'stok_minimum' => $minStok > 0 ? $minStok : 5,
                        'ruangan_id' => $ruanganId,
                    ]
                );

                // 4. Update/Create Batch Saldo Awal SAKTI
                if ($stokClean > 0) {
                    $existingBatch = $persediaan->batches()->where('no_batch', 1)->first();
                    if ($existingBatch) {
                        $existingBatch->update([
                            'jumlah_masuk' => $stokClean,
                            'sisa_stok'    => $stokClean,
                            'harga_satuan' => $hargaClean > 0 ? $hargaClean : $existingBatch->harga_satuan,
                        ]);
                    } else {
                        BatchPersediaan::create([
                            'persediaan_id' => $persediaan->id,
                            'no_batch'      => 1,
                            'no_referensi'  => 'SAKTI-' . date('Ymd'),
                            'no_faktur'     => 'SYNC-SAKTI-' . str_pad($persediaan->id, 3, '0', STR_PAD_LEFT),
                            'nota_dinas'    => 'Import Laporan SAKTI',
                            'supplier'      => 'Pengadaan Resmi APBN / SAKTI',
                            'tanggal_masuk' => now()->toDateString(),
                            'jumlah_masuk'  => $stokClean,
                            'harga_satuan'  => $hargaClean,
                            'sisa_stok'     => $stokClean,
                        ]);
                    }
                }

                $importedItems[] = $nama . " ({$stokClean} {$satuan})";
            }
        });

        return [
            'success'        => true,
            'message'        => 'Sinkronisasi persediaan SAKTI berhasil! Sebanyak ' . count($importedItems) . ' item berhasil diproses.',
            'imported_count' => count($importedItems),
            'items'          => $importedItems,
            'errors'         => $errors,
        ];
    }
}
