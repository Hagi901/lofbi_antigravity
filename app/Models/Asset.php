<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke tabel Category (Satu aset punya satu kategori)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke tabel SubCategory
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    // Relasi ke tabel Room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}