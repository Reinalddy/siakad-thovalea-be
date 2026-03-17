<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- AREA KHUSUS ADMIN ---
    Route::middleware(['role:Super Admin|Admin BAAK,sanctum'])->prefix('admin')->group(function () {
        // Manajemen Periode
        Route::get('/periods', [AcademicPeriodController::class, 'index']);
        Route::post('/periods', [AcademicPeriodController::class, 'store']);
        Route::delete('/periods/{id}', [AcademicPeriodController::class, 'destroy']);
        Route::put('/periods/{id}/set-active', [AcademicPeriodController::class, 'setActive']);
        Route::put('/periods/{id}', [AcademicPeriodController::class, 'update']);

        Route::get('/courses', [CourseController::class, 'index']);
        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{id}', [CourseController::class, 'update']);
        Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

        Route::get('/rooms', [RoomController::class, 'index']);
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{id}', [RoomController::class, 'update']);
        Route::delete('/rooms/{id}', [RoomController::class, 'destroy']);

        Route::get('/lecturers', [LecturerController::class, 'index']);
        Route::post('/lecturers', [LecturerController::class, 'store']);
        Route::put('/lecturers/{id}', [LecturerController::class, 'update']);
        Route::delete('/lecturers/{id}', [LecturerController::class, 'destroy']);

        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    });
});
