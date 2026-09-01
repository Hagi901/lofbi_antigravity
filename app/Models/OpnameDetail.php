<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameDetail extends Model
{
    protected $fillable = [
        'opname_sesi_id', 'persediaan_id', 'aset_id',
        'stok_buku', 'stok_fisik', 'selisih', 'satuan',
        'kondisi_aktual', 'jumlah_aktual', 'catatan',
    ];

    // ── Relasi ───────────────────────────────────────────────────────────

    public function sesi()
    {
        return $this->belongsTo(OpnameSesi::class, 'opname_sesi_id');
    }

    public function persediaan()
    {
        return $this->belongsTo(Persediaan::class);
    }

    public function aset()
    {
        return $this->belongsTo(Aset::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** True jika ada selisih */
    public function hasSelisih(): bool
    {
        return $this->selisih !== null && $this->selisih !== 0;
    }

    /** Label selisih dengan tanda (+/-) */
    public function selisihLabel(): string
    {
        if ($this->selisih === null) return '-';
        if ($this->selisih > 0) return '+' . $this->selisih;
        return (string) $this->selisih;
    }

    /** Badge class untuk selisih */
    public function selisihBadgeClass(): string
    {
        if ($this->selisih === null || $this->selisih === 0) {
            return 'bg-success-subtle text-success border-success';
        }
        if ($this->selisih > 0) {
            return 'bg-info-subtle text-info border-info';
        }
        return 'bg-danger-subtle text-danger border-danger';
    }
}
