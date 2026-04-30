<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\SignUp;
use App\Livewire\Common\AppointmentList;
use App\Livewire\Doctor\ClinicSettings;
use App\Livewire\Doctor\EditSchedule;
use App\Livewire\Doctor\Schedule;
use App\Livewire\Patient\Appointment;
use App\Livewire\Patient\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('login');
    Route::get('/register', SignUp::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/patient/dashboard', Dashboard::class)->name('patient.dashboard');
    Route::get('/patient/book-appointment', Appointment::class)->name('patient.book-appointment');

    Route::get('/receptionist/dashboard', App\Livewire\Receptionist\Dashboard::class)->name('receptionist.dashboard');
    Route::get('/receptionist/book-appointment', App\Livewire\Receptionist\Appointment::class)->name('receptionist.book-appointment');

    Route::get('/doctor/dashboard', App\Livewire\Doctor\Dashboard::class)->name('doctor.dashboard');
    Route::get('/doctor/schedule', Schedule::class)->name('doctor.schedule');
    Route::get('/doctor/schedule/edit/{id?}', EditSchedule::class)->name('doctor.schedule.edit');
    Route::get('/doctor/clinic-settings', ClinicSettings::class)->name('doctor.clinic-settings');

    Route::get('/appointments', AppointmentList::class)->name('appointments.index');

    // Fail-safe GET logout
    Route::get('/logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout.get');
});
