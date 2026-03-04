<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseScoreResource extends JsonResource
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
            'scores' => [
                'attendance' => $this->score_attendance,
                'assignment' => $this->score_assignment,
                'uts' => $this->score_uts,
                'uas' => $this->score_uas,
            ],
            'final' => [
                'numeric' => $this->final_score_numeric,
                'letter' => $this->final_score_letter,
                'weight' => $this->final_weight,
            ],
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'student' => collect(new StudentResource($this->whenLoaded('student')))->only(['id', 'nim', 'batch', 'user']),
            'period' => new KrsPeriodResource($this->whenLoaded('krsPeriod')),
        ];
    }
}
