<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'modul', 'aksi', 'detail',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
