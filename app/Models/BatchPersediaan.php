<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchPersediaan extends Model
{
    protected $fillable = [
        'persediaan_id', 'no_batch', 'no_referensi', 'no_faktur', 'nota_dinas',
        'supplier', 'tanggal_masuk', 'jumlah_masuk', 'harga_satuan', 'harga_belum_diisi', 'sisa_stok',
        'kode_barang_lengkap', 'kb_lvl1', 'kb_lvl2', 'kb_lvl3', 'kb_lvl4', 'kb_lvl5', 'kb_seq',
        'no_dokumen_sakti', 'no_bukti_sakti', 'tgl_dok', 'tgl_buku', 'upload_baris_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk'     => 'date',
            'harga_satuan'      => 'decimal:2',
            'harga_belum_diisi' => 'boolean',
            'tgl_dok'           => 'date',
            'tgl_buku'          => 'date',
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
