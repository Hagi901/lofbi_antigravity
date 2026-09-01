<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameSesi extends Model
{
    protected $fillable = [
        'ruangan_id', 'admin_id', 'tanggal', 'periode', 'keterangan', 'status',
        'approver_id', 'tanggal_persetujuan', 'catatan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'             => 'date',
            'tanggal_persetujuan' => 'date',
        ];
    }

    // ── Relasi ───────────────────────────────────────────────────────────

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function details()
    {
        return $this->hasMany(OpnameDetail::class, 'opname_sesi_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Label status untuk tampilan UI */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'                  => 'Draft',
            'menunggu_persetujuan'   => 'Menunggu Persetujuan',
            'disetujui'              => 'Disetujui',
            'ditolak'                => 'Ditolak',
            default                  => ucfirst($this->status),
        };
    }

    /** Badge Bootstrap class untuk status */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft'                  => 'bg-secondary-subtle text-secondary border-secondary',
            'menunggu_persetujuan'   => 'bg-warning-subtle text-warning border-warning',
            'disetujui'              => 'bg-success-subtle text-success border-success',
            'ditolak'                => 'bg-danger-subtle text-danger border-danger',
            default                  => 'bg-light text-muted border',
        };
    }

    /** Total selisih (positif = lebih, negatif = kurang) */
    public function totalSelisih(): int
    {
        return (int) $this->details()->sum('selisih');
    }

    /** Jumlah item yang ada selisih */
    public function jumlahSelisih(): int
    {
        return $this->details()->whereNotNull('selisih')->where('selisih', '!=', 0)->count();
    }
}
