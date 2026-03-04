<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MeController;
use App\Http\Controllers\StudentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Apply auth:sanctum logic where required
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', MeController::class);
});

// Assuming store is public for registration or requires specific middleware later.
Route::post('/students', [StudentController::class, 'store']);
