<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model UploadPaket
 *
 * Satu record per file yang diunggah.
 * Status paket diturunkan dari status baris-barisnya.
 *
 * @property int $id
 * @property int $diunggah_oleh
 * @property string $jenis 'sakti' | 'siman'
 * @property string $nama_file
 * @property string $path_file
 * @property int $jumlah_baris
 * @property string $status 'menunggu' | 'disetujui_sebagian' | 'disetujui' | 'ditolak'
 * @property string|null $catatan
 */
class UploadPaket extends Model
{
    protected $fillable = [
        'diunggah_oleh',
        'jenis',
        'nama_file',
        'path_file',
        'jumlah_baris',
        'status',
        'catatan',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────────

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function baris(): HasMany
    {
        return $this->hasMany(UploadBaris::class, 'upload_paket_id')->orderBy('nomor_baris');
    }

    // ── Helper Query ─────────────────────────────────────────────────────────

    public function barisMenunggu(): HasMany
    {
        return $this->hasMany(UploadBaris::class, 'upload_paket_id')->where('status', 'menunggu');
    }

    public function barisDisetujui(): HasMany
    {
        return $this->hasMany(UploadBaris::class, 'upload_paket_id')->where('status', 'disetujui');
    }

    public function barisDitolak(): HasMany
    {
        return $this->hasMany(UploadBaris::class, 'upload_paket_id')->where('status', 'ditolak');
    }

    // ── Status Refresh ───────────────────────────────────────────────────────

    /**
     * Hitung ulang status paket dari status baris dan simpan.
     * Panggil setelah setiap aksi approve/reject.
     */
    public function refreshStatus(): void
    {
        $total = $this->baris()->count();
        $disetujui = $this->baris()->where('status', 'disetujui')->count();
        $ditolak = $this->baris()->where('status', 'ditolak')->count();
        $menunggu = $this->baris()->where('status', 'menunggu')->count();

        if ($total === 0) {
            $status = 'menunggu';
        } elseif ($menunggu > 0) {
            // Masih ada baris yang menunggu
            if ($disetujui > 0 || $ditolak > 0) {
                $status = 'disetujui_sebagian';
            } else {
                $status = 'menunggu';
            }
        } elseif ($disetujui === $total) {
            $status = 'disetujui';
        } elseif ($ditolak === $total) {
            $status = 'ditolak';
        } else {
            // Campuran disetujui + ditolak, tidak ada menunggu
            $status = 'disetujui_sebagian';
        }

        $this->update(['status' => $status]);
    }

    // ── Accessor ─────────────────────────────────────────────────────────────

    /**
     * Label status yang ramah UI (bahasa Indonesia).
     */
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'Menunggu Validasi',
            'disetujui_sebagian' => 'Disetujui Sebagian',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Warna badge Tailwind untuk status (digunakan di Blade).
     */
    public function getWarnaBadgeAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'yellow',
            'disetujui_sebagian' => 'blue',
            'disetujui' => 'green',
            'ditolak' => 'red',
            default => 'gray',
        };
    }
}
