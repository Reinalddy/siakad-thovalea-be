<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentStat extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'max_sks_allowed'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
