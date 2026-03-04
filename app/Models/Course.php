<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'sks',
        'semester_type',
        'prerequisite_course_id'
    ];

    public function prerequisite()
    {
        return $this->belongsTo(Course::class, 'prerequisite_course_id');
    }

    public function curriculums()
    {
        return $this->belongsToMany(Curriculum::class);
    }
}
