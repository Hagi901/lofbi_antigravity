<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model UploadBaris
 *
 * Satu record per baris data dalam file upload (staging).
 * Setelah disetujui validator, baris ini dipromosikan ke tabel resmi.
 *
 * @property int    $id
 * @property int    $upload_paket_id
 * @property int    $nomor_baris
 * @property string $status          'menunggu' | 'disetujui' | 'ditolak'
 * @property string|null $deskripsi_revisi
 * @property int|null    $ditindak_oleh
 * @property string|null $ditindak_pada
 *
 * -- Field Persediaan SAKTI --
 * @property string|null $kode_barang_lengkap  (16 digit string, JANGAN dikonversi ke int/float)
 * @property string|null $kb_lvl1 .. $kb_lvl5, $kb_seq
 * @property string|null $nama_barang
 * @property string|null $satuan
 * @property int|null    $saldo_sistem
 * @property int|null    $hasil_opsik
 * @property string|null $kategori_sakti
 * @property float|null  $harga_satuan         (null jika belum diisi)
 * @property bool        $harga_belum_diisi
 * @property bool        $is_placeholder
 * @property bool        $perlu_perhatian
 * @property string|null $catatan_parser
 * @property int|null    $batch_persediaan_id  (terisi setelah disetujui)
 */
class UploadBaris extends Model
{
    protected $fillable = [
        'upload_paket_id',
        'nomor_baris',
        'status',
        'deskripsi_revisi',
        'ditindak_oleh',
        'ditindak_pada',

        // SAKTI fields
        'kode_barang_lengkap',
        'kb_lvl1', 'kb_lvl2', 'kb_lvl3', 'kb_lvl4', 'kb_lvl5', 'kb_seq',
        'nama_barang',
        'satuan',
        'saldo_sistem',
        'hasil_opsik',
        'kategori_sakti',
        'harga_satuan',
        'harga_belum_diisi',
        'no_dokumen',
        'no_bukti',
        'tgl_dok',
        'tgl_buku',

        // Quality flags
        'is_placeholder',
        'perlu_perhatian',
        'catatan_parser',

        // Reference to promoted batch
        'batch_persediaan_id',
    ];

    protected function casts(): array
    {
        return [
            'harga_satuan'      => 'decimal:2',
            'harga_belum_diisi' => 'boolean',
            'is_placeholder'    => 'boolean',
            'perlu_perhatian'   => 'boolean',
            'ditindak_pada'     => 'datetime',
            'tgl_dok'           => 'date',
            'tgl_buku'          => 'date',
        ];
    }

    // ── Relasi ───────────────────────────────────────────────────────────────

    public function paket(): BelongsTo
    {
        return $this->belongsTo(UploadPaket::class, 'upload_paket_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditindak_oleh');
    }

    public function batchPersediaan(): BelongsTo
    {
        return $this->belongsTo(BatchPersediaan::class, 'batch_persediaan_id');
    }

    // ── Helper ───────────────────────────────────────────────────────────────

    /**
     * Kunci unik untuk deteksi duplikasi saat re-upload revisi.
     * Persediaan: kode_barang_lengkap + no_dokumen (no_dokumen boleh null).
     */
    public function kunciUnik(): string
    {
        return ($this->kode_barang_lengkap ?? '') . '|' . ($this->no_dokumen ?? '');
    }

    /**
     * Label status yang ramah UI.
     */
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'menunggu'  => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak'   => 'Ditolak',
            default     => ucfirst($this->status),
        };
    }

    /**
     * Warna badge Tailwind untuk status baris.
     */
    public function getWarnaBadgeAttribute(): string
    {
        return match ($this->status) {
            'menunggu'  => 'yellow',
            'disetujui' => 'green',
            'ditolak'   => 'red',
            default     => 'gray',
        };
    }
}
