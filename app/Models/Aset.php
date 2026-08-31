<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    protected $fillable = [
        'jenis_barang_id', 'sub_kategori', 'kode_aset', 'merk', 'model', 'kondisi', 'ruangan_id',
        'nilai_perolehan', 'tanggal_perolehan', 'masa_manfaat', 'metode_penyusutan',
        'akumulasi_penyusutan', 'nilai_buku', 'terakhir_dihitung_semester', 'last_opname_date',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perolehan' => 'date',
            'nilai_perolehan' => 'decimal:2',
            'akumulasi_penyusutan' => 'decimal:2',
            'nilai_buku' => 'decimal:2',
        ];
    }

    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatAset::class);
    }

    // ── Accessors Kompatibilitas Blade Views ───────────────────────────

    public function getAssetCodeAttribute(): string
    {
        return $this->kode_aset ?? '';
    }

    public function getNameAttribute(): string
    {
        $namaGenerik = $this->jenisBarang?->nama_generik ?? '';
        $detail = trim(($this->merk ?? '') . ' ' . ($this->model ?? ''));
        return trim($namaGenerik . ($detail ? ' ' . $detail : '')) ?: ($this->kode_aset ?? 'Aset');
    }

    public function getCategoryAttribute()
    {
        return $this->jenisBarang?->kategori;
    }

    public function getSubCategoryAttribute()
    {
        return (object) ['name' => $this->sub_kategori ?? '-'];
    }

    public function getRoomAttribute()
    {
        return $this->ruangan;
    }

    public function getConditionAttribute(): string
    {
        return match ($this->kondisi) {
            'rusak_ringan' => 'Rusak Ringan',
            'rusak_berat'  => 'Rusak Berat',
            default        => 'Baik',
        };
    }

    public function getAcquisitionValueAttribute()
    {
        return $this->nilai_perolehan;
    }

    public function getUsefulLifeYearsAttribute()
    {
        return $this->masa_manfaat;
    }

    public function getBookValueAttribute()
    {
        return $this->nilai_buku;
    }
}
