<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GradeSetting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'grade_letter',
        'weight',
        'min_score',
        'max_score',
        'is_pass'
    ];

    protected $casts = [
        'weight' => 'float',
        'min_score' => 'float',
        'max_score' => 'float',
        'is_pass' => 'boolean'
    ];
}
