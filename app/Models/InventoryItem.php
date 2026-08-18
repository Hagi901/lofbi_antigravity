<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    // Buka kunci agar bisa menyimpan data
    protected $guarded = ['id'];

    // Relasi: Satu jenis barang bisa memiliki banyak Batch (tumpukan stok)
    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }
}