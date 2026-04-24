<?php

namespace App\Livewire\Doctor;

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

    public $confirmingScheduleDeletion = false;

    public $scheduleIdBeingDeleted = null;

    public function mount()
    {
        $this->loadSchedules();
    }

    public function loadSchedules()
    {
        $doctor = Auth::user()->doctor;

        if (! $doctor) {
            return;
        }

        $allSchedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Initialize empty array for all days 0-6
        $this->schedules = [
            0 => [], 1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [],
        ];

        foreach ($allSchedules as $schedule) {
            $this->schedules[$schedule->day_of_week][] = $schedule;
        }
    }

    public function confirmScheduleDeletion($id)
    {
        $this->confirmingScheduleDeletion = true;
        $this->scheduleIdBeingDeleted = $id;
    }

    public function cancelScheduleDeletion()
    {
        $this->confirmingScheduleDeletion = false;
        $this->scheduleIdBeingDeleted = null;
    }

    public function deleteSchedule()
    {
        $schedule = DoctorSchedule::findOrFail($this->scheduleIdBeingDeleted);

        // Ensure the doctor can only delete their own schedule
        if ($schedule->doctor_id !== Auth::user()->doctor->id) {
            $this->cancelScheduleDeletion();

            return;
        }

        $schedule->delete();
        $this->loadSchedules();
        $this->cancelScheduleDeletion();

        session()->flash('message', 'Schedule deleted successfully.');
    }

    public function render()
    {
        return view('livewire.doctor.schedule');
    }
}
