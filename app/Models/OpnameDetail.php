<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameDetail extends Model
{
    protected $fillable = ['opname_sesi_id', 'aset_id', 'persediaan_id', 'kondisi_aktual', 'jumlah_aktual', 'catatan'];

    public function sesi()
    {
        return $this->belongsTo(OpnameSesi::class, 'opname_sesi_id');
    }

    public function aset()
    {
        return $this->belongsTo(Aset::class, 'aset_id');
    }

    public function persediaan()
    {
        return $this->belongsTo(Persediaan::class, 'persediaan_id');
    }
}
