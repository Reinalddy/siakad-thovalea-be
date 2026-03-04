<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_bill_id',
        'transaction_id',
        'payment_method',
        'amount_paid',
        'paid_at',
        'status'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    public function studentBill()
    {
        return $this->belongsTo(StudentBill::class);
    }
}
