<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumResource extends JsonResource
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
            'name' => $this->name,
            'year' => $this->year,
            'is_active' => $this->is_active,
            'study_program' => new StudyProgramResource($this->whenLoaded('studyProgram')),
            'courses' => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}
