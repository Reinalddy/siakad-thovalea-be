<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GpaSummary extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'krs_period_id',
        'ips',
        'total_sks_semester',
        'ipk',
        'total_sks_cumulative'
    ];

    protected $casts = [
        'ips' => 'float',
        'ipk' => 'float',
        'total_sks_semester' => 'integer',
        'total_sks_cumulative' => 'integer'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function krsPeriod()
    {
        return $this->belongsTo(KrsPeriod::class);
    }
}
