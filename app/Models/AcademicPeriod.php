<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_akademik',
        'semester',
        'status',
        'krs_start',
        'krs_end',
        'nilai_start',
        'nilai_end',
        'ukt_start',
        'ukt_end',
    ];
}