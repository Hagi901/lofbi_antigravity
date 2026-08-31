<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPersediaan extends Model
{
    protected $fillable = [
        'persediaan_id', 'jenis', 'jumlah', 'tanggal', 'unit_kerja_penerima',
        'diajukan_oleh', 'status', 'diputuskan_oleh', 'catatan_penolakan',
        'tanggal_keputusan', 'harga_satuan', 'no_referensi', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_keputusan' => 'datetime',
        ];
    }

    public function persediaan()
    {
        return $this->belongsTo(Persediaan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diajukanOleh()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diputuskanOleh()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    public function detailPemotongan()
    {
        return $this->hasMany(DetailPemotonganBatch::class);
    }
}
