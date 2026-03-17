<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    // Sesuaikan kolom ini dengan file migration create_courses_table kamu
    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'sks',
        'semester_plot', // Semester plot default (1-8)
        'tipe',    // Wajib / Pilihan
    ];
}