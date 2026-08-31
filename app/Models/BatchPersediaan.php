<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchPersediaan extends Model
{
    protected $fillable = [
        'persediaan_id', 'no_batch', 'no_referensi', 'no_faktur', 'nota_dinas',
        'supplier', 'tanggal_masuk', 'jumlah_masuk', 'harga_satuan', 'sisa_stok',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'harga_satuan' => 'decimal:2',
        ];
    }

    public function persediaan()
    {
        return $this->belongsTo(Persediaan::class);
    }

    // ── Accessors Kompatibilitas ──────────────────────────────────────

    public function getQtyReceivedAttribute(): int
    {
        return (int) $this->jumlah_masuk;
    }

    public function getQtyRemainingAttribute(): int
    {
        return (int) $this->sisa_stok;
    }

    public function getPurchasePriceAttribute()
    {
        return $this->harga_satuan;
    }

    public function getBatchNumberAttribute(): string
    {
        return $this->no_batch ?? '';
    }
}
