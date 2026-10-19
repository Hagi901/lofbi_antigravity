<?php

namespace App\Services\Upload;

use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Notifikasi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\UploadBaris;
use App\Models\UploadPaket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * UploadService
 *
 * Menangani seluruh alur siklus hidup Upload Paket & Staging:
 * 1. Upload file baru -> Staging (upload_pakets & upload_baris)
 * 2. Notifikasi validator
 * 3. Validasi per baris (setujui / tolak dengan deskripsi)
 * 4. Setujui semua baris yang belum ditolak
 * 5. Promosi baris disetujui ke batch_persediaans resmi
 * 6. Re-upload file revisi (menggantikan baris ditolak tanpa menyentuh baris disetujui)
 * 7. Notifikasi ke pengunggah
 */
class UploadService
{
    public function __construct(
        protected SaktiStagingParser $saktiParser
    ) {}

    /**
     * Upload file baru ke tabel staging.
     *
     * @param UploadedFile $file
     * @param User $user
     * @param int|null $ruanganId
     * @param string|null $sheetName
     * @return UploadPaket
     * @throws ValidationException
     */
    public function uploadFile(UploadedFile $file, User $user, ?int $ruanganId = null, ?string $sheetName = null): UploadPaket
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $rejectedExtensions = config('upload_mapping.rejected_extensions', []);

        if (array_key_exists($ext, $rejectedExtensions)) {
            throw ValidationException::withMessages([
                'file' => $rejectedExtensions[$ext],
            ]);
        }

        $allowedExtensions = config('upload_mapping.allowed_extensions', ['xlsx', 'csv']);
        if (!in_array($ext, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => 'Format file tidak didukung. Harap unggah file .xlsx atau .csv.',
            ]);
        }

        // Simpan file fisik ke storage
        $storedPath = $file->store('uploads/' . date('Y/m'), 'local');
        $fullPath   = \Illuminate\Support\Facades\Storage::disk('local')->path($storedPath);

        // Parse data menggunakan SaktiStagingParser
        $parsedRows = $this->saktiParser->parse($fullPath, $sheetName);

        if (empty($parsedRows)) {
            throw ValidationException::withMessages([
                'file' => 'File kosong atau format data tidak dapat dibaca oleh sistem.',
            ]);
        }

        return DB::transaction(function () use ($user, $file, $storedPath, $parsedRows) {
            // 1. Buat paket upload
            $paket = UploadPaket::create([
                'diunggah_oleh' => $user->id,
                'jenis'         => 'sakti',
                'nama_file'     => $file->getClientOriginalName(),
                'path_file'     => $storedPath,
                'jumlah_baris'  => count($parsedRows),
                'status'        => 'menunggu',
            ]);

            // 2. Insert baris-baris staging
            foreach ($parsedRows as $row) {
                $row['upload_paket_id'] = $paket->id;
                UploadBaris::create($row);
            }

            // 3. Notifikasi ke seluruh pengguna dengan role 'validator'
            $validators = User::where('role', 'validator')->get();
            foreach ($validators as $val) {
                Notifikasi::create([
                    'penerima_id' => $val->id,
                    'jenis'       => 'upload_baru',
                    'judul'       => 'Paket Unggahan Baru: Persediaan SAKTI',
                    'pesan'       => $user->name . ' mengunggah berkas ' . $file->getClientOriginalName() . ' (' . count($parsedRows) . ' baris). Menunggu validasi.',
                    'tautan'      => route('upload.show', $paket->id),
                    'data'        => [
                        'paket_id'      => $paket->id,
                        'pengunggah_id' => $user->id,
                        'jumlah_baris'  => count($parsedRows),
                    ],
                ]);
            }

            // 4. Catat ke AuditLog
            AuditLog::create([
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'modul'     => 'Upload Data',
                'aksi'      => 'Upload Berkas SAKTI',
                'detail'    => 'Paket #' . $paket->id . ' (' . $file->getClientOriginalName() . ') diunggah dengan ' . count($parsedRows) . ' baris staging.',
            ]);

            return $paket;
        });
    }

    /**
     * Upload ulang file untuk revisi baris yang ditolak.
     * Baris dengan kunci unik yang sama menggantikan baris ditolak dan kembali berstatus 'menunggu'.
     * Baris yang sudah disetujui TIDAK disentuh dan TIDAK diduplikasi.
     *
     * @param UploadPaket $paket
     * @param UploadedFile $file
     * @param User $user
     * @param string|null $sheetName
     * @return UploadPaket
     * @throws ValidationException
     */
    public function revisiPaket(UploadPaket $paket, UploadedFile $file, User $user, ?string $sheetName = null): UploadPaket
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $rejectedExtensions = config('upload_mapping.rejected_extensions', []);

        if (array_key_exists($ext, $rejectedExtensions)) {
            throw ValidationException::withMessages([
                'file' => $rejectedExtensions[$ext],
            ]);
        }

        $storedPath = $file->store('uploads/' . date('Y/m'), 'local');
        $fullPath   = \Illuminate\Support\Facades\Storage::disk('local')->path($storedPath);

        $parsedRows = $this->saktiParser->parse($fullPath, $sheetName);
        if (empty($parsedRows)) {
            throw ValidationException::withMessages([
                'file' => 'File revisi kosong atau format tidak dapat dibaca.',
            ]);
        }

        return DB::transaction(function () use ($paket, $parsedRows, $user, $file, $storedPath) {
            $existingRows = $paket->baris()->get();
            $updatedCount = 0;
            $newCount = 0;

            foreach ($parsedRows as $newRow) {
                $kunciBaru = ($newRow['kode_barang_lengkap'] ?? '') . '|' . ($newRow['no_dokumen'] ?? '');

                // Cari baris lama dengan kunci sama
                $match = $existingRows->first(function ($r) use ($kunciBaru) {
                    return $r->kunciUnik() === $kunciBaru;
                });

                if ($match) {
                    if ($match->status === 'disetujui') {
                        // Sesuai aturan: baris yang sudah disetujui TIDAK boleh tersentuh dan TIDAK boleh terduplikasi
                        continue;
                    }

                    // Baris ditolak atau menunggu: gantikan isinya dengan data baru dan reset status ke 'menunggu'
                    $match->update([
                        'nama_barang'          => $newRow['nama_barang'],
                        'satuan'               => $newRow['satuan'],
                        'saldo_sistem'         => $newRow['saldo_sistem'],
                        'hasil_opsik'          => $newRow['hasil_opsik'],
                        'kategori_sakti'       => $newRow['kategori_sakti'],
                        'status'               => 'menunggu',
                        'deskripsi_revisi'     => null,
                        'ditindak_oleh'        => null,
                        'ditindak_pada'        => null,
                        'is_placeholder'       => $newRow['is_placeholder'],
                        'perlu_perhatian'      => $newRow['perlu_perhatian'],
                        'catatan_parser'       => $newRow['catatan_parser'],
                    ]);
                    $updatedCount++;
                } else {
                    // Baris baru dalam revisi
                    $newRow['upload_paket_id'] = $paket->id;
                    UploadBaris::create($newRow);
                    $newCount++;
                }
            }

            // Update info paket
            $paket->update([
                'path_file'    => $storedPath,
                'nama_file'    => $file->getClientOriginalName(),
                'jumlah_baris' => $paket->baris()->count(),
            ]);
            $paket->refreshStatus();

            // Notifikasi ke validator bahwa revisi telah diunggah
            $validators = User::where('role', 'validator')->get();
            foreach ($validators as $val) {
                Notifikasi::create([
                    'penerima_id' => $val->id,
                    'jenis'       => 'upload_baru',
                    'judul'       => 'Revisi Diunggah: Paket #' . $paket->id,
                    'pesan'       => $user->name . ' mengunggah revisi berkas ' . $file->getClientOriginalName() . '. Silakan verifikasi ulang.',
                    'tautan'      => route('upload.show', $paket->id),
                    'data'        => ['paket_id' => $paket->id, 'updated' => $updatedCount, 'new' => $newCount],
                ]);
            }

            AuditLog::create([
                'user_id'   => $user->id,
                'user_name' => $user->name,
                'modul'     => 'Upload Data',
                'aksi'      => 'Revisi Berkas SAKTI',
                'detail'    => 'Paket #' . $paket->id . ' direvisi: ' . $updatedCount . ' baris diperbarui, ' . $newCount . ' baris baru.',
            ]);

            return $paket;
        });
    }

    /**
     * Setujui satu baris staging dan promosikan ke tabel resmi batch_persediaans.
     *
     * @param UploadBaris $baris
     * @param User $validator
     * @param int|null $ruanganId
     * @return BatchPersediaan
     */
    public function setujuiBaris(UploadBaris $baris, User $validator, ?int $ruanganId = null): BatchPersediaan
    {
        if ($validator->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan memvalidasi baris.');
        }

        return DB::transaction(function () use ($baris, $validator, $ruanganId) {
            if ($baris->status === 'disetujui' && $baris->batch_persediaan_id) {
                return $baris->batchPersediaan;
            }

            // Tentukan ruangan default
            if (!$ruanganId) {
                $ruangan = Ruangan::where('nama', 'like', '%Gudang%')->first() ?? Ruangan::first();
                $ruanganId = $ruangan?->id;
            }

            // 1. Kategori
            $kategoriName = $baris->kategori_sakti ?: 'Persediaan BMN';
            $kategori = Kategori::firstOrCreate(
                ['nama' => $kategoriName, 'tipe' => 'persediaan']
            );

            // 2. Jenis Barang
            $namaBarang = $baris->nama_barang ?: 'Barang SAKTI ' . ($baris->kode_barang_lengkap ?? $baris->id);
            $jenisBarang = JenisBarang::firstOrCreate(
                ['nama_generik' => $namaBarang, 'kategori_id' => $kategori->id]
            );

            // 3. Master Persediaan
            $satuan = $baris->satuan ?: 'Unit';
            $persediaan = Persediaan::firstOrCreate(
                ['jenis_barang_id' => $jenisBarang->id, 'merk' => null],
                [
                    'satuan'       => $satuan,
                    'stok_minimum' => 5,
                    'ruangan_id'   => $ruanganId,
                ]
            );

            // 4. Hitung nomor batch berikutnya
            $maxBatch = (int) $persediaan->batches()->max('no_batch');
            $noBatch = $maxBatch + 1;

            $saldo = (int) ($baris->saldo_sistem ?? 0);

            // 5. Buat batch persediaan baru
            // SAKTI bahan_opname_fisik: harga_satuan = null, harga_belum_diisi = true
            $batch = BatchPersediaan::create([
                'persediaan_id'       => $persediaan->id,
                'no_batch'            => $noBatch,
                'no_referensi'        => 'SAKTI-UP' . $baris->upload_paket_id,
                'no_faktur'           => 'SAKTI-' . ($baris->kode_barang_lengkap ?? 'B' . $baris->id),
                'nota_dinas'          => 'Upload SAKTI #' . $baris->upload_paket_id,
                'supplier'            => 'Pengadaan Resmi APBN / SAKTI',
                'tanggal_masuk'       => now()->toDateString(),
                'jumlah_masuk'        => $saldo,
                'harga_satuan'        => $baris->harga_satuan, // NULL untuk file bahan_opname_fisik
                'harga_belum_diisi'   => $baris->harga_belum_diisi, // true
                'sisa_stok'           => $saldo,
                'kode_barang_lengkap' => $baris->kode_barang_lengkap,
                'kb_lvl1'             => $baris->kb_lvl1,
                'kb_lvl2'             => $baris->kb_lvl2,
                'kb_lvl3'             => $baris->kb_lvl3,
                'kb_lvl4'             => $baris->kb_lvl4,
                'kb_lvl5'             => $baris->kb_lvl5,
                'kb_seq'              => $baris->kb_seq,
                'no_dokumen_sakti'    => $baris->no_dokumen,
                'no_bukti_sakti'      => $baris->no_bukti,
                'tgl_dok'             => $baris->tgl_dok,
                'tgl_buku'            => $baris->tgl_buku,
                'upload_baris_id'     => $baris->id,
            ]);

            // 6. Update baris staging
            $baris->update([
                'status'              => 'disetujui',
                'ditindak_oleh'       => $validator->id,
                'ditindak_pada'       => now(),
                'batch_persediaan_id' => $batch->id,
            ]);

            // 7. Refresh status paket
            $paket = $baris->paket;
            $paket->refreshStatus();

            // 8. Kirim notifikasi 'disetujui' ke pengunggah jika paket selesai atau disetujui
            $this->kirimNotifikasiPersetujuanJikaPerlu($paket);

            return $batch;
        });
    }

    /**
     * Tolak satu baris staging dengan deskripsi revisi yang wajib diisi.
     *
     * @param UploadBaris $baris
     * @param User $validator
     * @param string $deskripsiRevisi
     */
    public function tolakBaris(UploadBaris $baris, User $validator, string $deskripsiRevisi): void
    {
        if ($validator->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menolak baris.');
        }

        $deskripsiClean = trim($deskripsiRevisi);
        if ($deskripsiClean === '') {
            throw ValidationException::withMessages([
                'deskripsi_revisi' => 'Deskripsi alasan penolakan/revisi wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($baris, $validator, $deskripsiClean) {
            $baris->update([
                'status'           => 'ditolak',
                'deskripsi_revisi' => $deskripsiClean,
                'ditindak_oleh'    => $validator->id,
                'ditindak_pada'    => now(),
            ]);

            $paket = $baris->paket;
            $paket->refreshStatus();

            // Notifikasi ke pengunggah: ada baris ditolak
            $pengunggah = $paket->pengunggah;
            if ($pengunggah) {
                Notifikasi::create([
                    'penerima_id' => $pengunggah->id,
                    'jenis'       => 'ada_ditolak',
                    'judul'       => 'Baris Ditolak pada Paket #' . $paket->id,
                    'pesan'       => 'Baris ke-' . $baris->nomor_baris . ' (' . ($baris->nama_barang ?: 'Kode ' . $baris->kode_barang_lengkap) . ') ditolak: ' . $deskripsiClean,
                    'tautan'      => route('upload.show', ['id' => $paket->id, 'filter' => 'ditolak']),
                    'data'        => [
                        'paket_id'         => $paket->id,
                        'nomor_baris'      => $baris->nomor_baris,
                        'deskripsi_revisi' => $deskripsiClean,
                    ],
                ]);
            }

            AuditLog::create([
                'user_id'   => $validator->id,
                'user_name' => $validator->name,
                'modul'     => 'Validasi Upload',
                'aksi'      => 'Tolak Baris',
                'detail'    => 'Baris #' . $baris->id . ' (Paket #' . $paket->id . ') ditolak: ' . $deskripsiClean,
            ]);
        });
    }

    /**
     * Setujui semua baris yang belum ditolak (status = 'menunggu') dalam satu transaksi.
     *
     * @param UploadPaket $paket
     * @param User $validator
     * @param int|null $ruanganId
     * @return int Jumlah baris yang berhasil disetujui
     */
    public function setujuiSemua(UploadPaket $paket, User $validator, ?int $ruanganId = null): int
    {
        if ($validator->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan memvalidasi baris.');
        }

        return DB::transaction(function () use ($paket, $validator, $ruanganId) {
            $pendingRows = $paket->baris()->where('status', 'menunggu')->get();
            $approvedCount = 0;

            foreach ($pendingRows as $baris) {
                $this->setujuiBaris($baris, $validator, $ruanganId);
                $approvedCount++;
            }

            $paket->refreshStatus();

            AuditLog::create([
                'user_id'   => $validator->id,
                'user_name' => $validator->name,
                'modul'     => 'Validasi Upload',
                'aksi'      => 'Setujui Semua Baris',
                'detail'    => 'Sebanyak ' . $approvedCount . ' baris pada Paket #' . $paket->id . ' disetujui sekaligus.',
            ]);

            return $approvedCount;
        });
    }

    /**
     * Kirim notifikasi 'disetujui' ke pengunggah ketika ada baris yang disetujui atau paket selesai.
     */
    private function kirimNotifikasiPersetujuanJikaPerlu(UploadPaket $paket): void
    {
        $pengunggah = $paket->pengunggah;
        if (!$pengunggah) {
            return;
        }

        $disetujuiCount = $paket->baris()->where('status', 'disetujui')->count();

        // Buat notifikasi dengan tautan langsung ke tabel Persediaan
        Notifikasi::create([
            'penerima_id' => $pengunggah->id,
            'jenis'       => 'disetujui',
            'judul'       => 'Data Disetujui: Paket #' . $paket->id,
            'pesan'       => 'Data pada paket ' . $paket->nama_file . ' telah disetujui (' . $disetujuiCount . ' baris). Klik Lihat Data untuk memeriksa inventaris.',
            'tautan'      => route('inventory.index', ['upload_paket_id' => $paket->id]),
            'data'        => [
                'paket_id'        => $paket->id,
                'disetujui_count' => $disetujuiCount,
                'status_paket'    => $paket->status,
            ],
        ]);
    }
}
