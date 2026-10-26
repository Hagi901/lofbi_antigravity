<?php

namespace App\Services\Opname;

use App\Models\Aset;
use App\Models\AuditLog;
use App\Models\OpnameDetail;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * OpnameService
 *
 * Menangani seluruh alur siklus hidup pemeriksaan fisik (stok opname).
 * Digunakan bersama oleh Web dan API Controller.
 */
class OpnameService
{
    /**
     * Buka sesi opname fisik baru dari Web interface:
     * - Status awal 'draft'
     * - Snapshot seluruh item persediaan aktif dan saldo buku saat ini
     * - Catat ke AuditLog
     */
    public function bukaSesiWeb(array $data, User $admin): OpnameSesi
    {
        $userId = $admin->id;
        $userName = $admin->name;

        return DB::transaction(function () use ($data, $userId, $userName) {
            $sesi = OpnameSesi::create([
                'ruangan_id' => $data['ruangan_id'] ?? null,
                'admin_id' => $userId,
                'tanggal' => $data['tanggal'],
                'periode' => $data['periode'],
                'keterangan' => $data['keterangan'] ?? null,
                'status' => 'draft',
            ]);

            $persediaans = Persediaan::with('batches')->get();
            foreach ($persediaans as $p) {
                $stokBuku = (int) $p->batches->sum('sisa_stok');
                OpnameDetail::create([
                    'opname_sesi_id' => $sesi->id,
                    'persediaan_id' => $p->id,
                    'stok_buku' => $stokBuku,
                    'stok_fisik' => null,
                    'selisih' => null,
                    'satuan' => $p->satuan ?? '-',
                    'catatan' => null,
                ]);
            }

            AuditLog::create([
                'user_id' => $userId,
                'user_name' => $userName,
                'modul' => 'Opname',
                'aksi' => 'Buka Sesi Opname',
                'detail' => 'Buka sesi opname '.$data['periode'].' - '.$persediaans->count().' jenis barang di-snapshot',
            ]);

            return $sesi;
        });
    }

    /**
     * Simpan sesi opname fisik dari API:
     * - Menerima array data dan list details langsung dari request.
     */
    public function simpanSesiApi(array $data, User $admin): OpnameSesi
    {
        $userId = $admin->id;

        return DB::transaction(function () use ($data, $userId) {
            $sesi = OpnameSesi::create([
                'ruangan_id' => $data['ruangan_id'],
                'admin_id' => $userId,
                'tanggal' => $data['tanggal'],
                'status' => $data['status'] ?? 'selesai',
            ]);

            $sesi->details()->createMany($data['details']);

            return $sesi;
        });
    }

    /**
     * Simpan hasil hitung fisik, hitung selisih, dan ubah status ke 'menunggu_persetujuan'.
     */
    public function simpanHasilFisik(OpnameSesi $sesi, array $stokFisik, array $catatan, User $user): OpnameSesi
    {
        if (! in_array($sesi->status, ['draft', 'ditolak'])) {
            throw ValidationException::withMessages([
                'opname' => 'Sesi ini tidak dapat diedit.',
            ]);
        }

        $userId = $user->id;
        $userName = $user->name;

        return DB::transaction(function () use ($sesi, $stokFisik, $catatan, $userId, $userName) {
            foreach ($stokFisik as $detailId => $jumlahFisik) {
                $detail = OpnameDetail::where('id', $detailId)
                    ->where('opname_sesi_id', $sesi->id)
                    ->firstOrFail();

                $selisih = (int) $jumlahFisik - (int) $detail->stok_buku;

                $detail->update([
                    'stok_fisik' => (int) $jumlahFisik,
                    'selisih' => $selisih,
                    'catatan' => $catatan[$detailId] ?? null,
                ]);
            }

            $sesi->update(['status' => 'menunggu_persetujuan']);

            AuditLog::create([
                'user_id' => $userId,
                'user_name' => $userName,
                'modul' => 'Opname',
                'aksi' => 'Submit Hasil Fisik',
                'detail' => 'Sesi #'.$sesi->id.' diajukan ke approver',
            ]);

            return $sesi->fresh();
        });
    }

    /**
     * Menyetujui sesi opname dan membuat transaksi penyesuaian otomatis.
     *
     * @throws HttpException bila $approver bukan validator.
     * @throws ValidationException
     */
    public function setujui(OpnameSesi $sesi, User $approver): OpnameSesi
    {
        if ($approver->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menyetujui sesi opname.');
        }

        if ($sesi->status !== 'menunggu_persetujuan') {
            throw ValidationException::withMessages([
                'opname' => 'Sesi ini tidak dalam status menunggu persetujuan.',
            ]);
        }

        $approverId = $approver->id;
        $approverName = $approver->name;

        return DB::transaction(function () use ($sesi, $approverId, $approverName) {
            foreach ($sesi->details as $detail) {
                if ($detail->selisih === null || $detail->selisih === 0) {
                    continue;
                }

                $p = $detail->persediaan;
                if (! $p) {
                    continue;
                }

                // Hitung harga satuan rata-rata dari batch aktif yang memiliki harga valid
                $batchesValid = $p->batches->filter(fn ($b) => ! $b->harga_belum_diisi && ! is_null($b->harga_satuan));
                $totalNilai = $batchesValid->sum(fn ($b) => $b->sisa_stok * $b->harga_satuan);
                $totalStok = $batchesValid->sum('sisa_stok');
                $hargaRata = $totalStok > 0 ? round($totalNilai / $totalStok) : 0;

                TransaksiPersediaan::create([
                    'persediaan_id' => $detail->persediaan_id,
                    'jenis' => $detail->selisih > 0 ? 'masuk' : 'keluar',
                    'jumlah' => abs($detail->selisih),
                    'tanggal' => $sesi->tanggal,
                    'unit_kerja_penerima' => 'Penyesuaian Opname Fisik - '.$sesi->periode,
                    'diajukan_oleh' => $sesi->admin_id,
                    'diputuskan_oleh' => $approverId,
                    'status' => 'disetujui',
                    'catatan_penolakan' => null,
                    'tanggal_keputusan' => now(),
                ]);

                // Update sisa_stok batch FIFO jika selisih kurang
                if ($detail->selisih < 0) {
                    $kurang = abs($detail->selisih);
                    foreach ($p->batches()->orderBy('tanggal_masuk')->get() as $batch) {
                        if ($kurang <= 0) {
                            break;
                        }
                        $potongan = min($batch->sisa_stok, $kurang);
                        $batch->decrement('sisa_stok', $potongan);
                        $kurang -= $potongan;
                    }
                } elseif ($detail->selisih > 0) {
                    // Selisih lebih: tambahkan ke batch paling baru
                    $batchTerbaru = $p->batches()->latest('tanggal_masuk')->first();
                    if ($batchTerbaru) {
                        $batchTerbaru->increment('sisa_stok', $detail->selisih);
                    }
                }
            }

            $sesi->update([
                'status' => 'disetujui',
                'approver_id' => $approverId,
                'tanggal_persetujuan' => now()->toDateString(),
            ]);

            AuditLog::create([
                'user_id' => $approverId,
                'user_name' => $approverName,
                'modul' => 'Opname',
                'aksi' => 'Setujui Opname',
                'detail' => 'Sesi #'.$sesi->id.' ('.$sesi->periode.') disetujui. Jurnal penyesuaian dibuat.',
            ]);

            return $sesi->fresh();
        });
    }

    /**
     * Menolak sesi opname.
     *
     * @throws HttpException bila $approver bukan validator.
     * @throws ValidationException
     */
    public function tolak(OpnameSesi $sesi, string $catatan, User $approver): OpnameSesi
    {
        if ($approver->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menolak sesi opname.');
        }

        if ($sesi->status !== 'menunggu_persetujuan') {
            throw ValidationException::withMessages([
                'opname' => 'Sesi ini tidak dalam status menunggu persetujuan.',
            ]);
        }

        $approverId = $approver->id;
        $approverName = $approver->name;

        return DB::transaction(function () use ($sesi, $catatan, $approverId, $approverName) {
            $sesi->update([
                'status' => 'ditolak',
                'catatan_penolakan' => $catatan,
                'approver_id' => $approverId,
            ]);

            AuditLog::create([
                'user_id' => $approverId,
                'user_name' => $approverName,
                'modul' => 'Opname',
                'aksi' => 'Tolak Opname',
                'detail' => 'Sesi #'.$sesi->id.' ditolak: '.$catatan,
            ]);

            return $sesi->fresh();
        });
    }

    /**
     * Hitung nilai buku dan nilai selisih untuk tampilan detail sesi opname.
     */
    public function hitungNilai(OpnameSesi $sesi): array
    {
        $jumlahTerdampak = 0;
        $totalNilaiBuku = 0;
        $totalNilaiSelisih = 0;

        foreach ($sesi->details as $detail) {
            $p = $detail->persediaan;
            if (! $p) {
                continue;
            }

            $batchKosong = $p->batches->filter(fn ($b) => $b->harga_belum_diisi || is_null($b->harga_satuan));
            if ($batchKosong->isNotEmpty()) {
                $jumlahTerdampak++;
            }

            $batchesValid = $p->batches->filter(fn ($b) => ! $b->harga_belum_diisi && ! is_null($b->harga_satuan));
            $stokValid = $batchesValid->sum('sisa_stok');
            $nilaiValid = $batchesValid->sum(fn ($b) => $b->sisa_stok * $b->harga_satuan);
            $hargaRata = $stokValid > 0 ? ($nilaiValid / $stokValid) : 0;

            $totalNilaiBuku += ($detail->stok_buku * $hargaRata);
            if ($detail->selisih !== null) {
                $totalNilaiSelisih += ($detail->selisih * $hargaRata);
            }
        }

        return [
            'totalNilaiBuku' => $totalNilaiBuku,
            'totalNilaiSelisih' => $totalNilaiSelisih,
            'hasHargaBelumDiisi' => ($jumlahTerdampak > 0),
            'jumlahTerdampak' => $jumlahTerdampak,
        ];
    }

    /**
     * Mengambil daftar aset dan persediaan per ruangan untuk pengisian opname API.
     */
    public function getReferensiRuangan(Ruangan $ruangan): array
    {
        return [
            'ruangan' => $ruangan,
            'aset' => Aset::with('jenisBarang')
                ->where('ruangan_id', $ruangan->id)
                ->orderBy('kode_aset')
                ->get(),
            'persediaan' => Persediaan::with(['jenisBarang', 'batches'])
                ->where('ruangan_id', $ruangan->id)
                ->get()
                ->map(fn ($item) => $item->setAttribute('total_stok', $item->batches->sum('sisa_stok'))),
        ];
    }
}
