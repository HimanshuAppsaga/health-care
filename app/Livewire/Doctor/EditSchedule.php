<?php

namespace App\Livewire\Doctor;

use App\Models\DoctorSchedule;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Schedule | ClinicOS')]
class EditSchedule extends Component
{
    public $scheduleId = null;

    #[Validate('required|integer|between:0,6')]
    public $day_of_week;

    #[Validate('required')]
    public $start_time;

    #[Validate('required')]
    public $end_time;

    #[Validate('required|integer|min:1')]
    public $max_patients = 1;

    #[Validate('required|integer|min:5')]
    public $slot_duration = 15;

    public function mount($id = null)
    {
        if ($id) {
            $schedule = DoctorSchedule::findOrFail($id);

            // Security check
            if ($schedule->doctor_id !== Auth::user()->doctor->id) {
                return redirect()->route('doctor.schedule');
            }

            $this->scheduleId = $id;
            $this->day_of_week = $schedule->day_of_week;
            $this->start_time = $schedule->start_time;
            $this->end_time = $schedule->end_time;
            $this->max_patients = $schedule->max_patients;
            $this->slot_duration = $schedule->slot_duration;
        }
    }

    public function save()
    {
        $this->validate();

        $doctor = Auth::user()->doctor;

        if (! $doctor) {
            return;
        }

        $data = [
            'doctor_id' => $doctor->id,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'max_patients' => $this->max_patients,
            'slot_duration' => $this->slot_duration,
        ];

        if ($this->scheduleId) {
            $schedule = DoctorSchedule::findOrFail($this->scheduleId);
            $schedule->update($data);
            session()->flash('message', 'Schedule updated successfully.');
        } else {
            DoctorSchedule::create($data);
            session()->flash('message', 'Schedule created successfully.');
        }

        return redirect()->route('doctor.schedule');
    }

    public function render()
    {
        return view('livewire.doctor.editSchedule');
    }
}
