<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentBillResource;
use App\Models\StudentBill;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseController
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Admin mass-generates bills.
     */
    public function generateMassBills(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_category_id' => ['required', 'exists:payment_categories,id'],
            'krs_period_id' => ['nullable', 'exists:krs_periods,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'batch' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            $validated = $validator->validated();

            $count = $this->paymentService->generateMassBills(
                $validated['payment_category_id'],
                $validated['krs_period_id'] ?? null,
                $validated['amount'],
                $validated['due_date'],
                $validated['study_program_id'] ?? null,
                $validated['batch'] ?? null
            );

            return $this->sendResponse(['total_generated' => $count], 'Mass billing generated successfully.', 201);
        } catch (\Exception $e) {
            Log::error('Mass Billing Failure: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            return $this->sendError('Server Error Generating Bills', [], 500);
        }
    }

    /**
     * Student views their personal bills.
     */
    public function myBills(Request $request): JsonResponse
    {
        // Assuming strict auth passes the user. Resolving linked student id.
        $studentId = $request->user()->student->id ?? null;

        if (!$studentId) {
            return $this->sendError('Unauthorized', ['user' => ['User is not correctly linked to a student profile.']], 403);
        }

        $bills = StudentBill::with(['paymentCategory', 'krsPeriod'])
            ->where('student_id', $studentId)
            ->orderBy('due_date', 'desc')
            ->get();

        return $this->sendResponse(StudentBillResource::collection($bills), 'Student bills retrieved.');
    }

    /**
     * Student generates a payment token/VA.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_bill_id' => ['required', 'exists:student_bills,id'],
            'payment_method' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
        }

        try {
            $validated = $validator->validated();

            // Verify bill actually belongs to requester student!
            $studentId = $request->user()->student->id;
            $bill = StudentBill::where('id', $validated['student_bill_id'])->where('student_id', $studentId)->first();

            if (!$bill) {
                return $this->sendError('Unauthorized', ['student_bill_id' => ['Bill does not belong to the authenticated student.']], 403);
            }

            $checkoutData = $this->paymentService->createCheckoutUrl($bill->id, $validated['payment_method']);

            return $this->sendResponse($checkoutData, 'Checkout transaction initiated successfully.', 201);

        } catch (\Exception $e) {
            Log::error('Checkout Failed: ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            return $this->sendError($e->getMessage(), [], 422); // Bad logic or already paid error
        }
    }

    /**
     * Webhook Endpoint designed to be hit strictly by Payment Gateways.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        // Hypothetical signature verification would occur here before proceeding!
        // $signature = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . config('services.midtrans.server_key'));
        // if ($signature !== $request->header('signature')) { return 403 }

        try {
            $payload = $request->all();

            // For mock reasons we expect payload to have `transaction_id` and `transaction_status`.
            $this->paymentService->handleWebhook($payload);

            return response()->json(['status' => 'success', 'message' => 'Webhook received and processed cleanly.']);
        } catch (\Exception $e) {
            Log::error('Webhook Handling Failed: ' . $e->getMessage(), ['payload' => $request->all(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
            // Still return 200/400 gracefully so Gateway doesn't repeatedly hammer server if it's a structural 500 error on our side.
            return response()->json(['status' => 'error', 'message' => 'Internal processing failure. Logged.'], 400);
        }
    }
}
