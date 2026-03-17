<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'kode_ruang',
        'nama_ruang',
        'kapasitas',
        'jenis_ruang',
    ];
}
