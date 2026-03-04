<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_letter' => $this->grade_letter,
            'weight' => $this->weight,
            'min_score' => $this->min_score,
            'max_score' => $this->max_score,
            'is_pass' => $this->is_pass,
        ];
    }
}
