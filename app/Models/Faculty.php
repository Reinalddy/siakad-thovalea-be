<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['code', 'name'];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class);
    }
}
