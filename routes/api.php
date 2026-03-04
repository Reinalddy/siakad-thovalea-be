<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\ScheduleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Apply auth:sanctum logic where required
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);

    // Main Entities
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('curriculums', CurriculumController::class);
    Route::apiResource('schedules', ScheduleController::class);

    // KRS Registration Engine
    Route::prefix('krs')->group(function () {
        // Students, Lecturers, Admins
        Route::get('available-courses', [\App\Http\Controllers\KrsController::class, 'getAvailableCourses'])
            ->middleware('role:Student|Lecturer|Admin');

        // Only Students can Enroll
        Route::post('enroll', [\App\Http\Controllers\KrsController::class, 'enroll'])
            ->middleware('role:Student');

        // Only Lecturers (Advisors) or Admins can Approve
        Route::patch('approve/{student_id}', [\App\Http\Controllers\KrsController::class, 'approve'])
            ->middleware('role:Lecturer|Admin');
    });

    // Grading, KHS, & Transcript Engine
    Route::prefix('grades')->group(function () {
        // Public settings viewing
        Route::get('settings', [\App\Http\Controllers\ScoreController::class, 'getSettings']);

        // Only Lecturers input grades bulk
        Route::post('input-batch', [\App\Http\Controllers\ScoreController::class, 'inputBatch'])
            ->middleware('role:Lecturer|Admin');

        // Students view their specific KHS/IPS for a period
        Route::get('khs/{student_id}/{krs_period_id}', [\App\Http\Controllers\ScoreController::class, 'getKhs'])
            ->middleware('role:Student|Admin|Lecturer');

        // Students view their total IPK transcript
        Route::get('transcript/{student_id}', [\App\Http\Controllers\ScoreController::class, 'getTranscript'])
            ->middleware('role:Student|Admin|Lecturer');
    });

    // UKT & Financial Management Engine
    Route::prefix('finance')->group(function () {
        // Admin mass-billing generator
        Route::post('generate-bills', [\App\Http\Controllers\PaymentController::class, 'generateMassBills'])
            ->middleware('role:Admin');

        // Student views their own generated bills
        Route::get('my-bills', [\App\Http\Controllers\PaymentController::class, 'myBills'])
            ->middleware('role:Student');

        // Student initiates a checkout process (Returns mock VA/URL)
        Route::post('checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])
            ->middleware('role:Student');
    });
});

// Assuming store is public for registration or requires specific middleware later.
Route::post('/students', [StudentController::class, 'store']);

// Public Webhook for Payment Gateways (Outside auth:sanctum but protected by signature later)
Route::post('finance/webhook', [\App\Http\Controllers\PaymentController::class, 'handleWebhook']);
