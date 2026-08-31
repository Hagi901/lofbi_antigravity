<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBarang extends Model
{
    protected $fillable = ['nama_generik', 'kategori_id'];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function persediaans()
    {
        return $this->hasMany(Persediaan::class);
    }

    public function asets()
    {
        return $this->hasMany(Aset::class);
    }
}
