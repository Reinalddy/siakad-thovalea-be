<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CourseScore extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'schedule_id',
        'krs_period_id',
        'score_attendance',
        'score_assignment',
        'score_uts',
        'score_uas',
        'final_score_numeric',
        'final_score_letter',
        'final_weight'
    ];

    protected $casts = [
        'score_attendance' => 'float',
        'score_assignment' => 'float',
        'score_uts' => 'float',
        'score_uas' => 'float',
        'final_score_numeric' => 'float',
        'final_weight' => 'float'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function krsPeriod()
    {
        return $this->belongsTo(KrsPeriod::class);
    }
}
