<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\SignUp;
use App\Livewire\Common\AppointmentList;
use App\Livewire\Doctor\AssignRole;
use App\Livewire\Doctor\ClinicDetail;
use App\Livewire\Doctor\ClinicEdit;
use App\Livewire\Doctor\ClinicSettings;
use App\Livewire\Doctor\DoctorDetail;
use App\Livewire\Doctor\DoctorEdit;
use App\Livewire\Receptionist\Appointment;
use App\Livewire\Receptionist\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', Login::class)->name('login');
    Route::get('/register', SignUp::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    // Receptionist routes
    Route::middleware('role:receptionist')->group(function () {
        Route::get('/receptionist/dashboard', Dashboard::class)->name('receptionist.dashboard');
        Route::get('/receptionist/book-appointment', Appointment::class)->name('receptionist.book-appointment');
    });

    // Doctor routes
    Route::middleware('role:doctor')->group(function () {
        Route::get('/doctor/dashboard', App\Livewire\Doctor\Dashboard::class)->name('doctor.dashboard');
        Route::get('/doctor/assign-role', AssignRole::class)->name('doctor.assign-role');
        Route::get('/doctor/clinic-settings', ClinicSettings::class)->name('doctor.clinic-settings');
        Route::get('/doctor/clinic-details/{id}', ClinicDetail::class)->name('doctor.clinic.detail');
        Route::get('/doctor/clinic-details/{id}/edit', ClinicEdit::class)->name('doctor.clinic.edit');
        Route::get('/doctor/profile/{id}', DoctorDetail::class)->name('doctor.profile.detail');
        Route::get('/doctor/profile/{id}/edit', DoctorEdit::class)->name('doctor.profile.edit');
    });

    // Patient routes
    Route::middleware('role:patient')->group(function () {
        Route::get('/patient/dashboard', App\Livewire\Patient\Dashboard::class)->name('patient.dashboard');
    });

    Route::get('/appointments', AppointmentList::class)->name('appointments.index');

    // Fail-safe GET logout
    Route::get('/logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout.get');
});
