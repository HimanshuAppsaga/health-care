<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\SidebarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sidebar', [SidebarController::class, 'index'])
    ->middleware('auth:sanctum');

Route::post('/appointments/book', [AppointmentController::class, 'store'])->middleware('api.key');

// Clinic API Routes
Route::prefix('v1/clinic')->middleware('api.key')->group(function () {
    Route::get('/doctors', [\App\Http\Controllers\Api\ClinicController::class, 'doctors']);
    Route::get('/stats', [\App\Http\Controllers\Api\ClinicController::class, 'stats']);
});
