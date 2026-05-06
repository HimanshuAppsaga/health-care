<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\QueueController;
use Illuminate\Support\Facades\Route;

Route::post('/appointments/book', [AppointmentController::class, 'store'])->middleware('api.key');

// Secure Multi-Tenant Clinic API Routes
Route::middleware('api.key')->group(function () {
    Route::get('/clinic/details', [ClinicController::class, 'details']);
    Route::get('/doctors', [DoctorController::class, 'index']);

    // Queue Manager API
    Route::prefix('queue')->group(function () {
        Route::get('/live', [QueueController::class, 'live']);
        Route::post('/call-next', [QueueController::class, 'callNext']);
        Route::post('/transfer', [QueueController::class, 'transfer']);
    });

    Route::post('/v1/patient/check-token', [App\Http\Controllers\Api\V1\Patient\TokenController::class, 'checkToken']);

});
