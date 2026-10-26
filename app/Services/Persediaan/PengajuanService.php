<?php

namespace App\Services\Persediaan;

use App\Models\AuditLog;
use App\Models\BatchPersediaan;
use App\Models\DetailPemotonganBatch;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * PengajuanService
 *
 * Menangani seluruh alur pengajuan barang keluar dan pemotongan stok FIFO.
 * Digunakan bersama oleh Web dan API Controller.
 */
class PengajuanService
{
    /**
     * Buat transaksi pengajuan barang keluar (status 'menunggu').
     */
    public function ajukan(Persediaan $persediaan, array $data, User $user, bool $logAudit = true): TransaksiPersediaan
    {
        $userId = $user->id;
        $userName = $user->name;
        $qty = (int) $data['jumlah'];

        return DB::transaction(function () use ($persediaan, $data, $userId, $userName, $qty, $logAudit) {
            $transaksi = TransaksiPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'diajukan_oleh' => $userId,
                'jenis' => 'keluar',
                'jumlah' => $qty,
                'tanggal' => $data['tanggal'] ?? now()->toDateString(),
                'status' => 'menunggu',
                'unit_kerja_penerima' => $data['unit_kerja_penerima'],
            ]);

            if ($logAudit) {
                AuditLog::create([
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'modul' => 'Persediaan',
                    'aksi' => 'Pengajuan Keluar',
                    'detail' => 'Mengajukan pengeluaran '.$qty.' '.($persediaan->satuan ?? 'unit').' '.$persediaan->name.' untuk '.$data['unit_kerja_penerima'].' - menunggu persetujuan Validator.',
                ]);
            }

            return $transaksi;
        });
    }

    /**
     * Menyetujui pengajuan barang keluar dan memotong stok batch secara FIFO.
     *
     * Opsi yang didukung:
     * - 'orderBy': string ('no_batch' untuk API) atau array (['tanggal_masuk' => 'asc', 'id' => 'asc'] untuk Web).
     * - 'recordDetail': bool (true untuk mencatat DetailPemotonganBatch pada API).
     * - 'logAudit': bool (true untuk mencatat riwayat pemotongan ke AuditLog pada Web).
     *
     * @throws HttpException bila $approver bukan validator.
     * @throws ValidationException
     */
    public function setujui(TransaksiPersediaan $transaksi, User $approver, array $options = []): TransaksiPersediaan
    {
        if ($approver->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menyetujui pengajuan barang keluar.');
        }

        if ($transaksi->status !== 'menunggu' || $transaksi->jenis !== 'keluar') {
            throw ValidationException::withMessages([
                'approve' => 'Pengajuan tidak dapat diproses atau sudah pernah ditindaklanjuti.',
            ]);
        }

        $orderBy = $options['orderBy'] ?? ['tanggal_masuk' => 'asc', 'id' => 'asc'];
        $recordDetail = (bool) ($options['recordDetail'] ?? false);
        $logAudit = (bool) ($options['logAudit'] ?? false);

        $approverId = $approver->id;
        $approverName = $approver->name;

        return DB::transaction(function () use ($transaksi, $approverId, $approverName, $orderBy, $recordDetail, $logAudit) {
            $persediaan = $transaksi->persediaan;
            $qtyDiminta = (int) $transaksi->jumlah;

            $query = BatchPersediaan::where('persediaan_id', $transaksi->persediaan_id)
                ->where('sisa_stok', '>', 0);

            if (is_array($orderBy)) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            } else {
                $query->orderBy($orderBy);
            }

            $batches = $query->lockForUpdate()->get();

            if ($batches->sum('sisa_stok') < $qtyDiminta) {
                throw ValidationException::withMessages([
                    'approve' => 'Stok tidak mencukupi untuk menyetujui pengajuan.',
                ]);
            }

            $sisaKebutuhan = $qtyDiminta;
            $rincianPemotongan = [];

            foreach ($batches as $batch) {
                if ($sisaKebutuhan <= 0) {
                    break;
                }

                $ambil = min($batch->sisa_stok, $sisaKebutuhan);
                $batch->decrement('sisa_stok', $ambil);

                if ($recordDetail) {
                    DetailPemotonganBatch::create([
                        'transaksi_persediaan_id' => $transaksi->id,
                        'batch_id' => $batch->id,
                        'jumlah_diambil' => $ambil,
                        'harga_satuan_saat_itu' => $batch->harga_satuan,
                    ]);
                }

                $sisaKebutuhan -= $ambil;
                $rincianPemotongan[] = 'Batch #'.$batch->no_batch." (-{$ambil})";
            }

            $transaksi->update([
                'status' => 'disetujui',
                'diputuskan_oleh' => $approverId,
                'tanggal_keputusan' => now(),
            ]);

            if ($logAudit) {
                AuditLog::create([
                    'user_id' => $approverId,
                    'user_name' => $approverName,
                    'modul' => 'Persediaan',
                    'aksi' => 'Setujui Keluar',
                    'detail' => 'Menyetujui pengeluaran '.$qtyDiminta.' '.($persediaan->satuan ?? 'unit').' '.($persediaan->name ?? 'item').'. FIFO: '.implode(', ', $rincianPemotongan),
                ]);
            }

            return $transaksi;
        });
    }

    /**
     * Menolak pengajuan barang keluar.
     *
     * @throws HttpException bila $approver bukan validator.
     * @throws ValidationException
     */
    public function tolak(TransaksiPersediaan $transaksi, string $alasan, User $approver, bool $logAudit = false): TransaksiPersediaan
    {
        if ($approver->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menolak pengajuan barang keluar.');
        }

        if ($transaksi->status !== 'menunggu' || $transaksi->jenis !== 'keluar') {
            throw ValidationException::withMessages([
                'reject' => 'Pengajuan tidak dapat diproses atau sudah pernah ditindaklanjuti.',
            ]);
        }

        $approverId = $approver->id;
        $approverName = $approver->name;

        return DB::transaction(function () use ($transaksi, $alasan, $approverId, $approverName, $logAudit) {
            $transaksi->update([
                'status' => 'ditolak',
                'diputuskan_oleh' => $approverId,
                'tanggal_keputusan' => now(),
                'catatan_penolakan' => $alasan,
            ]);

            if ($logAudit) {
                AuditLog::create([
                    'user_id' => $approverId,
                    'user_name' => $approverName,
                    'modul' => 'Persediaan',
                    'aksi' => 'Tolak Keluar',
                    'detail' => 'Menolak pengajuan pengeluaran '.$transaksi->jumlah.' unit '.($transaksi->persediaan?->name ?? '#'.$transaksi->persediaan_id).'. Alasan: '.$alasan,
                ]);
            }

            return $transaksi;
        });
    }
}
