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

    // ── Kalkulasi Penyusutan Garis Lurus — Metode SIMAN ───────────────────────

    /**
     * Hitung penyusutan aktual per semester (Garis Lurus — SIMAN).
     *
     * Rumus SIMAN:
     *   Total Semester       = Masa Manfaat × 2
     *   Beban per Semester   = Nilai Perolehan / Total Semester  (Residu = 0)
     *   Semester Berjalan    = bulan berjalan ÷ 6  (dibulatkan ke bawah)
     *   Akumulasi            = Beban per Semester × Semester Berjalan
     *   Nilai Buku           = Nilai Perolehan − Akumulasi
     *                          → Dibatasi minimum Rp 1 (lock constraint SIMAN)
     *
     * @return array [semester_berjalan, total_semester, susut_per_semester, susut_per_tahun, akumulasi, nilai_buku, persen_susut]
     */
    public function hitungPenyusutanGarisLurus(): array
    {
        $nilaiPerolehan = (float) ($this->nilai_perolehan ?? 0);
        $masaManfaat    = (int)   ($this->masa_manfaat ?? 0);

        if ($nilaiPerolehan <= 0 || $masaManfaat <= 0) {
            return [
                'semester_berjalan'  => 0,
                'total_semester'     => 0,
                'susut_per_semester' => 0,
                'susut_per_tahun'    => 0,
                'tahun_berjalan'     => 0,
                'akumulasi'          => 0,
                'nilai_buku'         => $nilaiPerolehan,
                'persen_susut'       => 0,
            ];
        }

        $tglPerolehan = $this->tanggal_perolehan
            ? Carbon::parse($this->tanggal_perolehan)
            : Carbon::now();

        // Hitung bulan yang sudah berjalan sejak perolehan
        $bulanBerjalan = (int) $tglPerolehan->diffInMonths(Carbon::now());

        // Semester yang sudah berjalan (1 semester = 6 bulan), maks = total semester
        $totalSemester    = $masaManfaat * 2;
        $semesterBerjalan = min((int) floor($bulanBerjalan / 6), $totalSemester);
        $tahunBerjalan    = min((int) floor($bulanBerjalan / 12), $masaManfaat);

        // ── Rumus SIMAN: Residu = 0, lock constraint Rp 1 ───────────────
        $susutPerSemester = $nilaiPerolehan / $totalSemester;
        $susutPerTahun    = $susutPerSemester * 2;

        // Akumulasi tidak boleh melebihi (Nilai Perolehan − 1)
        $akumulasiMaks = $nilaiPerolehan - 1;
        $akumulasi     = min($susutPerSemester * $semesterBerjalan, $akumulasiMaks);

        // Nilai Buku minimum Rp 1 (lock constraint SIMAN)
        $nilaiBuku = max($nilaiPerolehan - $akumulasi, 1);

        // Persentase penyusutan dihitung dari nilai yang dapat disusutkan
        $persenSusut = $akumulasiMaks > 0
            ? round(($akumulasi / $akumulasiMaks) * 100, 1)
            : 0;

        return [
            'semester_berjalan'  => $semesterBerjalan,
            'total_semester'     => $totalSemester,
            'susut_per_semester' => $susutPerSemester,
            'susut_per_tahun'    => $susutPerTahun,
            'tahun_berjalan'     => $tahunBerjalan,
            'akumulasi'          => $akumulasi,
            'nilai_buku'         => $nilaiBuku,
            'persen_susut'       => $persenSusut,
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

    /** Penyusutan per semester */
    public function getSusutPerSemesterAttribute(): float
    {
        return $this->hitungPenyusutanGarisLurus()['susut_per_semester'];
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

