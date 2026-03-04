<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentWebhookLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_id',
        'raw_payload',
        'processed_at'
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'processed_at' => 'datetime'
    ];
}
