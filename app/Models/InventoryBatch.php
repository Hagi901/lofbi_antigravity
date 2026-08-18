<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use HasFactory;

    // Buka kunci agar bisa menyimpan data
    protected $guarded = ['id'];

    // Relasi: Batch ini milik barang yang mana?
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}