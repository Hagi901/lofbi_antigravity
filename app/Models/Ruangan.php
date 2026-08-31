<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $fillable = ['nama', 'gedung'];

    public function getNameAttribute(): string
    {
        return $this->nama ?? '';
    }
}
