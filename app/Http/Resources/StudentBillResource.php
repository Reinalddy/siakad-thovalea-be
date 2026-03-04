<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentBillResource extends JsonResource
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
            'amount' => $this->amount,
            'due_date' => $this->due_date->format('Y-m-d'),
            'status' => $this->status,
            'category' => $this->whenLoaded('paymentCategory', function () {
                return [
                    'id' => $this->paymentCategory->id,
                    'name' => $this->paymentCategory->name,
                ];
            }),
            'period' => new KrsPeriodResource($this->whenLoaded('krsPeriod')),
            'student' => collect(new StudentResource($this->whenLoaded('student')))->only(['id', 'nim', 'batch', 'user']),
        ];
    }
}
