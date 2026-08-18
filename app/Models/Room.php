<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi: Satu ruangan bisa memiliki banyak aset (hasMany)
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}