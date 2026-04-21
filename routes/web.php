<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\SignUp;
use Illuminate\Support\Facades\Route;



Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('login');
    Route::get('/register', SignUp::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\ClinicAdmin\Dashboard::class)->name('admin.dashboard');
    
    // Staff Management
    Route::get('/staff', \App\Livewire\ClinicAdmin\Staff\Staff::class)->name('admin.staff.index');
    Route::get('/staff/create', \App\Livewire\ClinicAdmin\Staff\CreateStaff::class)->name('admin.staff.create');
    Route::get('/staff/{id}/edit', \App\Livewire\ClinicAdmin\Staff\EditStaff::class)->name('admin.staff.edit');
    Route::get('/staff/{id}', \App\Livewire\ClinicAdmin\Staff\DetailStaff::class)->name('admin.staff.detail');
});
