<?php

use App\Http\Controllers\AcademicPeriodController;
use App\Http\Controllers\AuthController;
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
    });
});
