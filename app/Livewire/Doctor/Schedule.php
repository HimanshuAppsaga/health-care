<?php

namespace App\Livewire\Doctor;

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
        $doctor = $user->ensureDoctorProfileExists();

        if (! $doctor) {
            return;
        }

        $this->schedules = $doctor->working_hours ?? [
            1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 0 => [],
        ];
    }

    public function render()
    {
        return view('livewire.doctor.schedule');
    }
}
