<?php

namespace App\Livewire\Doctor;

use App\Events\ScheduleUpdated;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Edit Schedule | ClinicOS')]
class EditSchedule extends Component
{
    public $scheduleId = null;

    public $day_of_week;

    public $start_time;

    public $end_time;

    public $max_patients = 1;

    public $slot_duration = 15;

    public $start_hour = '09';

    public $start_min = '00';

    public $start_period = 'AM';

    public $end_hour = '05';

    public $end_min = '00';

    public $end_period = 'PM';

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

            // Parse start time
            $start = Carbon::parse($schedule->start_time);
            $this->start_hour = $start->format('h');
            $this->start_min = $start->format('i');
            $this->start_period = $start->format('A');

            // Parse end time
            $end = Carbon::parse($schedule->end_time);
            $this->end_hour = $end->format('h');
            $this->end_min = $end->format('i');
            $this->end_period = $end->format('A');

            $this->max_patients = $schedule->max_patients;
            $this->slot_duration = $schedule->slot_duration;
        }
    }

    public function save()
    {
        $this->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_hour' => 'required',
            'start_min' => 'required',
            'start_period' => 'required',
            'end_hour' => 'required',
            'end_min' => 'required',
            'end_period' => 'required',
        ]);

        $doctor = Auth::user()->doctor;

        if (! $doctor) {
            return;
        }

        // Re-assemble 24h format
        $this->start_time = Carbon::createFromFormat('h:i A', "{$this->start_hour}:{$this->start_min} {$this->start_period}")->format('H:i:s');
        $this->end_time = Carbon::createFromFormat('h:i A', "{$this->end_hour}:{$this->end_min} {$this->end_period}")->format('H:i:s');

        $data = [
            'doctor_id' => $doctor->id,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'max_patients' => $this->max_patients,
            'slot_duration' => $this->slot_duration,
        ];

        $clinicId = $doctor->clinic_id;

        if ($this->scheduleId) {
            $schedule = DoctorSchedule::findOrFail($this->scheduleId);
            $schedule->update($data);
            if ($clinicId) {
                broadcast(new ScheduleUpdated($clinicId, 'updated'))->toOthers();
            }
            session()->flash('message', 'Schedule updated successfully.');
        } else {
            DoctorSchedule::create($data);
            if ($clinicId) {
                broadcast(new ScheduleUpdated($clinicId, 'created'))->toOthers();
            }
            session()->flash('message', 'Schedule created successfully.');
        }

        return redirect()->route('doctor.schedule');
    }

    public function render()
    {
        return view('livewire.doctor.editSchedule');
    }
}
