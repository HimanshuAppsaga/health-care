<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\Mobile\AppointmentHistoryController;
use App\Http\Controllers\Api\Mobile\TodayAppointmentController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\V1\Patient\TokenController;
use App\Services\SidebarConfig;
use Illuminate\Support\Facades\Route;

// Mobile Authentication APIs protected by clinic API key validation
Route::middleware('api.key.validate')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::post('/appointments/book', [AppointmentController::class, 'store'])->middleware('api.key');

// Secure Multi-Tenant Clinic API Routes
Route::middleware('api.key')->group(function () {
    Route::get('/clinic/details', [ClinicController::class, 'details']);
    Route::get('/doctors', [DoctorController::class, 'index']);

    // Queue Manager API
    Route::prefix('queue')->group(function () {
        Route::get('/live', [QueueController::class, 'live']);
        Route::get('/waiting-list', [QueueController::class, 'waitingList']);
        Route::post('/call-next', [QueueController::class, 'callNext']);
        Route::post('/transfer', [QueueController::class, 'transfer']);
    });

    Route::post('/v1/patient/check-token', [TokenController::class, 'checkToken']);
    Route::get('/today-schedule', [ScheduleController::class, 'todaySchedule']);
    Route::get('/today-appointments', [TodayAppointmentController::class, 'index']);
    Route::get('/appointment-history', [AppointmentHistoryController::class, 'index']);
});

Route::middleware('auth:sanctum')->get('/sidebar', function () {
    $user = auth()->user();
    $role = $user->role?->name ?? '';

    return response()->json([
        'role' => $role,
        'menu' => SidebarConfig::getMenuForRole($role),
    ]);
});
