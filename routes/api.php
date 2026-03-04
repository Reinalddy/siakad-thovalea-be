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
});

// Assuming store is public for registration or requires specific middleware later.
Route::post('/students', [StudentController::class, 'store']);
