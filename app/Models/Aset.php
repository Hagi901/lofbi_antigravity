<?php

namespace App\Models;

use Carbon\Carbon;
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

    // ── Kalkulasi Penyusutan Garis Lurus (Dinamis) ─────────────────────────

    /**
     * Hitung penyusutan aktual berdasarkan tanggal hari ini (Garis Lurus).
     * Mengembalikan array: [tahun_berjalan, susut_per_tahun, akumulasi, nilai_buku, persen_susut]
     */
    public function hitungPenyusutanGarisLurus(): array
    {
        $nilaiPerolehan = (float) ($this->nilai_perolehan ?? 0);
        $masaManfaat    = (int)   ($this->masa_manfaat ?? 0);

        if ($nilaiPerolehan <= 0 || $masaManfaat <= 0) {
            return [
                'tahun_berjalan' => 0,
                'susut_per_tahun' => 0,
                'akumulasi'      => 0,
                'nilai_buku'     => $nilaiPerolehan,
                'persen_susut'   => 0,
            ];
        }

        // Hitung tahun yang sudah berjalan (bulat ke bawah, maks = masa manfaat)
        $tglPerolehan  = $this->tanggal_perolehan
            ? Carbon::parse($this->tanggal_perolehan)
            : Carbon::now();
        $tahunBerjalan = min(
            (int) $tglPerolehan->diffInYears(Carbon::now()),
            $masaManfaat
        );

        // Rumus Garis Lurus
        $susutPerTahun = $nilaiPerolehan / $masaManfaat;
        $akumulasi     = min($susutPerTahun * $tahunBerjalan, $nilaiPerolehan);
        $nilaiBuku     = max($nilaiPerolehan - $akumulasi, 0);
        $persenSusut   = round(($akumulasi / $nilaiPerolehan) * 100, 1);

        return [
            'tahun_berjalan'  => $tahunBerjalan,
            'susut_per_tahun' => $susutPerTahun,
            'akumulasi'       => $akumulasi,
            'nilai_buku'      => $nilaiBuku,
            'persen_susut'    => $persenSusut,
        ];
    }

    /** Nilai Buku dinamis (hasil kalkulasi hari ini) */
    public function getNilaiBukuDinamisAttribute(): float
    {
        return $this->hitungPenyusutanGarisLurus()['nilai_buku'];
    }

    /** Akumulasi penyusutan dinamis (hasil kalkulasi hari ini) */
    public function getAkumulasiDinamisAttribute(): float
    {
        return $this->hitungPenyusutanGarisLurus()['akumulasi'];
    }

    /** Penyusutan per tahun */
    public function getSusutPerTahunAttribute(): float
    {
        return $this->hitungPenyusutanGarisLurus()['susut_per_tahun'];
    }

    /** Persentase sudah terdepresiasi (0–100) */
    public function getPersenSusutAttribute(): float
    {
        return $this->hitungPenyusutanGarisLurus()['persen_susut'];
    }

    /** Tahun ke berapa dari masa manfaat */
    public function getTahunBerjalanAttribute(): int
    {
        return $this->hitungPenyusutanGarisLurus()['tahun_berjalan'];
    }
}
