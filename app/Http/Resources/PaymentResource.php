<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'transaction_id' => $this->transaction_id,
            'payment_method' => $this->payment_method,
            'amount_paid' => $this->amount_paid,
            'paid_at' => $this->paid_at ? $this->paid_at->toDateTimeString() : null,
            'status' => $this->status,
            'bill' => new StudentBillResource($this->whenLoaded('studentBill')),
        ];
    }
}
