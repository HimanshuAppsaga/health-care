<?php

use App\Http\Controllers\Api\SidebarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/sidebar', [SidebarController::class, 'index'])
    ->middleware('auth:sanctum');
