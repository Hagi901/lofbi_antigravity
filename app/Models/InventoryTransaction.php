<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    protected $guarded = ['id'];

    // Relasi balik ke batch
    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }
}