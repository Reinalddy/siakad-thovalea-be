<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dosen_pa_id', // Tambahan dari kamu
        'nim',
        'prodi',
        'angkatan',
        'status_mahasiswa',
        'ipk',         // Tambahan dari kamu
    ];

    // Relasi ke User (Satu mahasiswa punya satu akun login)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dosen_pa()
    {
        return $this->belongsTo(Lecturer::class, 'dosen_pa_id');
    }
}