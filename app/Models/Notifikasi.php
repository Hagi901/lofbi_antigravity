<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Notifikasi
 *
 * @property int $id
 * @property int $penerima_id
 * @property string $jenis   'upload_baru' | 'ada_ditolak' | 'disetujui'
 * @property string $judul
 * @property string $pesan
 * @property string|null $tautan
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $dibaca_pada
 */
class Notifikasi extends Model
{
    protected $fillable = [
        'penerima_id',
        'jenis',
        'judul',
        'pesan',
        'tautan',
        'data',
        'dibaca_pada',
    ];

    protected function casts(): array
    {
        return [
            'data'        => 'array',
            'dibaca_pada' => 'datetime',
        ];
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function scopeBelumDibaca($query)
    {
        return $query->whereNull('dibaca_pada');
    }

    public function markAsRead(): void
    {
        if (is_null($this->dibaca_pada)) {
            $this->update(['dibaca_pada' => now()]);
        }
    }
}
