<?php

namespace App\Livewire\Doctor;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Schedule Management | ClinicOS')]
class Schedule extends Component
{
    public $schedules = [];

    public function getListeners()
    {
        return [
            'echo:schedule-updates.1,ScheduleUpdated' => 'loadSchedules',
        ];
    }

    public function mount()
    {
        $this->loadSchedules();
    }

    public function loadSchedules()
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if (! $doctor && $user->hasRole('doctor')) {
            Doctor::create([
                'user_id' => $user->id,
                'clinic_id' => $user->clinic_id,
                'specialization' => 'General',
                'qualification' => 'MBBS',
                'experience_years' => 0,
                'consultation_fee' => 0,
            ]);
        }

        if (! $doctor) {
            return;
        }

        $allSchedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Initialize empty array for all days 0-6
        $this->schedules = [
            1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 0 => [],
        ];

        foreach ($allSchedules as $schedule) {
            $this->schedules[$schedule->day_of_week][] = $schedule;
        }
    }

    public function render()
    {
        return view('livewire.doctor.schedule');
    }
}
