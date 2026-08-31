<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persediaan extends Model
{
    protected $fillable = ['jenis_barang_id', 'merk', 'satuan', 'stok_minimum', 'ruangan_id'];

    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function batches()
    {
        return $this->hasMany(BatchPersediaan::class);
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiPersediaan::class);
    }

    public function transaksis()
    {
        return $this->hasMany(TransaksiPersediaan::class);
    }

    // ── Accessors Kompatibilitas Blade Views ───────────────────────────

    public function getNameAttribute(): string
    {
        $namaGenerik = $this->jenisBarang?->nama_generik ?? '';
        return trim($namaGenerik . ($this->merk ? ' (' . $this->merk . ')' : '')) ?: 'Persediaan #' . $this->id;
    }

    public function getItemCodeAttribute(): string
    {
        return 'INV-' . str_pad((string)$this->id, 3, '0', STR_PAD_LEFT);
    }

    public function getBatchesSumQtyRemainingAttribute(): int
    {
        return (int) $this->batches()->sum('sisa_stok');
    }

    public function getTotalStokAttribute(): int
    {
        return (int) $this->batches()->sum('sisa_stok');
    }
}
