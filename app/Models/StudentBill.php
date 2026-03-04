<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StudentBill extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'student_id',
        'payment_category_id',
        'krs_period_id',
        'amount',
        'due_date',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentCategory()
    {
        return $this->belongsTo(PaymentCategory::class);
    }

    public function krsPeriod()
    {
        return $this->belongsTo(KrsPeriod::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
