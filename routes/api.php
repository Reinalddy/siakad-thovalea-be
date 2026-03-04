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
});

// Assuming store is public for registration or requires specific middleware later.
Route::post('/students', [StudentController::class, 'store']);
