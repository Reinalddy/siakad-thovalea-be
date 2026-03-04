<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentWebhookLog;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Mass generate bills for an entire batch or specific study program.
     */
    public function generateMassBills(
        string $paymentCategoryId,
        ?string $krsPeriodId,
        float $amount,
        string $dueDate,
        ?string $studyProgramId = null,
        ?string $batch = null
    ): int {
        $query = Student::query();

        if ($studyProgramId) {
            $query->where('prodi_id', $studyProgramId);
        }

        if ($batch) {
            $query->where('batch', $batch);
        }

        $students = $query->get();
        $generatedCount = 0;

        foreach ($students as $student) {
            // Prevent duplicate UKT billing exacts by checking uniqueness 
            // of category + student + active period combo.
            $exists = StudentBill::where('student_id', $student->id)
                ->where('payment_category_id', $paymentCategoryId)
                ->where('krs_period_id', $krsPeriodId)
                ->exists();

            if (!$exists) {
                StudentBill::create([
                    'student_id' => $student->id,
                    'payment_category_id' => $paymentCategoryId,
                    'krs_period_id' => $krsPeriodId,
                    'amount' => $amount,
                    'due_date' => $dueDate,
                    'status' => 'Unpaid'
                ]);
                $generatedCount++;
            }
        }

        return $generatedCount;
    }

    /**
     * Mock a Payment Gateway Checkout (Returns a dummy payment URL or VA)
     */
    public function createCheckoutUrl(string $studentBillId, string $method): array
    {
        $bill = StudentBill::findOrFail($studentBillId);

        if ($bill->status === 'Paid') {
            throw new \Exception('Bill is already completely paid.');
        }

        // Mocking an external transaction ID that midtrans/xendit would provide
        $gatewayTransactionId = 'MOCK-' . strtoupper(Str::random(10));

        $payment = Payment::create([
            'student_bill_id' => $bill->id,
            'transaction_id' => $gatewayTransactionId,
            'payment_method' => $method,
            'amount_paid' => $bill->amount, // assuming full payment for simplicity
            'status' => 'Pending'
        ]);

        return [
            'transaction_id' => $payment->transaction_id,
            'payment_url' => 'https://mock-gateway.siakad.local/pay/' . $payment->transaction_id,
            'virtual_account' => '8888' . random_int(10000000, 99999999)
        ];
    }

    /**
     * Process incoming Server-to-Server Webhook. Contains strict DB locking.
     */
    public function handleWebhook(array $payload): void
    {
        $transactionId = $payload['transaction_id'] ?? null;
        $status = $payload['transaction_status'] ?? null; // e.g., 'settlement' or 'success'

        if (!$transactionId) {
            throw new \Exception('Invalid Webhook Payload: Missing transaction_id');
        }

        // 1. Audit Log exactly what PG sent
        PaymentWebhookLog::create([
            'transaction_id' => $transactionId,
            'raw_payload' => $payload,
            'processed_at' => now()
        ]);

        // 2. Strict ACID Transaction
        DB::transaction(function () use ($transactionId, $status) {
            // Lock row exactly to prevent race conditions from duplicate webhooks
            $payment = Payment::where('transaction_id', $transactionId)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'Success') {
                return; // Idempotent check, already successfully processed
            }

            if (in_array($status, ['success', 'settlement'])) {
                $payment->update([
                    'status' => 'Success',
                    'paid_at' => now()
                ]);

                // Propagate upstream to Bill
                $bill = StudentBill::where('id', $payment->student_bill_id)->lockForUpdate()->first();
                if ($bill) {
                    $bill->update(['status' => 'Paid']);
                }
            } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
                $payment->update([
                    'status' => 'Failed'
                ]);
                // Bill remains Unpaid
            }
        });
    }

    /**
     * Helper to verify if a student is financially blocked for KRS
     */
    public function canStudentEnroll(string $studentId, string $targetKrsPeriodId): bool
    {
        // For strict rules: if ANY 'UKT' bill linked to this period (or past) is Unpaid/Partial, block.
        // Assuming we look for 'UKT' by a name lookup for simplicity, or we check ALL unpaid bills.
        $unpaidBillsCount = StudentBill::where('student_id', $studentId)
            ->whereIn('status', ['Unpaid', 'Partial', 'Overdue'])
            ->whereHas('paymentCategory', function ($query) {
                $query->where('name', 'UKT'); // Strict filter for UKT strictly blocking KRS
            })
            // Target the semester they are trying to enroll in OR previous overdue ones
            ->where(function ($q) use ($targetKrsPeriodId) {
                $q->where('krs_period_id', $targetKrsPeriodId)     // Current period owing
                    ->orWhere('status', 'Overdue');                  // Or strictly severely overdue
            })
            ->count();

        return $unpaidBillsCount === 0;
    }
}
