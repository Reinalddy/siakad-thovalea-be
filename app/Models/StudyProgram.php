<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudyProgram extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['faculty_id', 'code', 'name', 'degree'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
