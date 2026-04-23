<?php

namespace App\Livewire\Doctor;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Doctor Dashboard | ClinicOS')]
class Dashboard extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $doctor = Auth::user()->doctor;

        if (! $doctor) {
            return view('livewire.doctor.dashboard', [
                'totalAppointments' => 0,
                'pendingPatients' => 0,
                'completedToday' => 0,
                'todaysAppointments' => collect([]),
            ]);
        }

        $todaysAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $totalAppointments = $todaysAppointments->count();
        $completedToday = $todaysAppointments->where('status', 'completed')->count();
        $pendingPatients = $todaysAppointments->whereIn('status', ['confirmed', 'pending'])->count();

        return view('livewire.doctor.dashboard', [
            'totalAppointments' => $totalAppointments,
            'pendingPatients' => $pendingPatients,
            'completedToday' => $completedToday,
            'todaysAppointments' => $todaysAppointments,
        ]);
    }
}
