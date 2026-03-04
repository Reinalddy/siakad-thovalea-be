<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpaSummaryResource extends JsonResource
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
            'ips' => $this->ips,
            'total_sks_semester' => $this->total_sks_semester,
            'ipk' => $this->ipk,
            'total_sks_cumulative' => $this->total_sks_cumulative,
            'period' => new KrsPeriodResource($this->whenLoaded('krsPeriod')),
            'student' => collect(new StudentResource($this->whenLoaded('student')))->only(['id', 'nim', 'batch', 'user']),
        ];
    }
}
