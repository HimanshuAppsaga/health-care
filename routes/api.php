<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\SidebarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sidebar', [SidebarController::class, 'index'])
    ->middleware('auth:sanctum');

Route::post('/appointments/book', [AppointmentController::class, 'store'])->middleware('api.key');

// Secure Multi-Tenant Clinic API Routes
Route::middleware('api.key')->group(function () {
    Route::get('/clinic/details', [ClinicController::class, 'details']);
    Route::get('/doctors', [DoctorController::class, 'index']);

    // Existing routes if needed
    Route::get('/v1/clinic/stats', [ClinicController::class, 'stats']);
});
