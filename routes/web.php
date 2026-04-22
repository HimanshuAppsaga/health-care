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
    Route::get('/patient/dashboard', App\Livewire\Patient\Dashboard::class)->name('patient.dashboard');
    Route::get('/patient/book-appointment', App\Livewire\Patient\Appointment::class)->name('patient.book-appointment');

    // Fail-safe GET logout
    Route::get('/logout', function () {
        auth()->logout();   
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout.get');
});
