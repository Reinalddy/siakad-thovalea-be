<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KrsItemResource extends JsonResource
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
            'status' => $this->status,
            'period' => new KrsPeriodResource($this->whenLoaded('krsPeriod')),
            'schedule' => new ScheduleResource($this->whenLoaded('schedule')),
            'student' => $this->whenLoaded('student') ?
                collect((new StudentResource($this->student))->toArray($request))->only(['id', 'nim', 'batch', 'user'])
                : null,
        ];
    }
}
