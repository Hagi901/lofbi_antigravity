<?php

namespace App\Console\Commands;

use App\Models\Notifikasi;
use App\Models\UploadPaket;
use Illuminate\Console\Command;

class RapikanNotifikasiUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lofbi:rapikan-notifikasi-upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rapikan notifikasi persetujuan unggahan yang menumpuk, menyisakan satu per paket per penerima dengan data terbarui.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $notifikasis = Notifikasi::where('jenis', 'disetujui')->get();

        $grouped = $notifikasis->groupBy(function ($notif) {
            $paketId = $notif->data['paket_id'] ?? 0;

            return $notif->penerima_id.'_'.$paketId;
        });

        $deletedCount = 0;

        foreach ($grouped as $items) {
            if ($items->isEmpty()) {
                continue;
            }

            // Urutkan berdasarkan ID terbaru (notifikasi paling mutakhir)
            $sorted = $items->sortByDesc('id');
            $kept = $sorted->first();
            $toDelete = $sorted->slice(1);

            if ($toDelete->isNotEmpty()) {
                $deletedCount += $toDelete->count();
                Notifikasi::whereIn('id', $toDelete->pluck('id'))->delete();
            }

            // Perbarui data notifikasi yang disisakan berdasarkan status upload_baris saat ini
            $paketId = $kept->data['paket_id'] ?? null;
            if ($paketId) {
                $paket = UploadPaket::find($paketId);
                if ($paket) {
                    $disetujuiCount = $paket->baris()->where('status', 'disetujui')->count();
                    $totalBaris = $paket->jumlah_baris ?: $paket->baris()->count();
                    $namaFile = $paket->nama_file;

                    $pesan = $disetujuiCount.' dari '.$totalBaris.' baris pada paket '.$namaFile.' telah disetujui';

                    $data = $kept->data ?? [];
                    $data['paket_id'] = $paket->id;
                    $data['nama_file'] = $namaFile;
                    $data['disetujui_count'] = $disetujuiCount;
                    $data['total_baris'] = $totalBaris;
                    $data['status_paket'] = $paket->status;

                    $kept->update([
                        'pesan' => $pesan,
                        'data' => $data,
                    ]);
                }
            }
        }

        $this->info("Pembersihan selesai. {$deletedCount} notifikasi duplikat berhasil dihapus.");

        return Command::SUCCESS;
    }
}
