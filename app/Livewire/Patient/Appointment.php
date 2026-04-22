<?php

namespace App\Livewire\Patient;

use App\Models\Appointment as AppointmentModel;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Appointment extends Component
{
    public $step = 1;

    // Selection properties
    public $selectedClinicId;

    public $selectedDoctorId;

    public $selectedDate;

    public $selectedSlot;

    public $reason;

    public $notes;

    // UI Data
    public $clinics = [];

    public $doctors = [];

    public $availableSlots = [];

    // Summary
    public $selectedClinic;

    public $selectedDoctor;

    public $fee = 0;

    public $tax = 0;

    public $total = 0;

    public function mount()
    {
        $this->clinics = Clinic::where('is_active', true)->get();
        $this->selectedDate = Carbon::today()->format('Y-m-d');
    }

    public function updatedSelectedClinicId($value)
    {
        $this->selectedClinic = Clinic::find($value);
        $this->doctors = Doctor::with('user')
            ->where('clinic_id', $value)
            ->get();

        $this->selectedDoctorId = null;
        $this->selectedDoctor = null;
        $this->fee = 0;
        $this->updateTotal();
    }

    public function updatedSelectedDoctorId($value)
    {
        $this->selectedDoctor = Doctor::with('user')->find($value);
        if ($this->selectedDoctor) {
            $this->fee = $this->selectedDoctor->consultation_fee;
        }
        $this->updateTotal();
        $this->generateSlots();
    }

    public function updatedSelectedDate()
    {
        $this->generateSlots();
    }

    public function generateSlots()
    {
        $this->availableSlots = [];
        if (! $this->selectedDoctorId || ! $this->selectedDate) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)

        // Database uses 0-6 or 1-7? Check schema or model.
        // Typically Laravel/Carbon uses 0=Sunday, 1=Monday...
        // Let's check DoctorSchedule day_of_week

        $schedule = DoctorSchedule::where('doctor_id', $this->selectedDoctorId)
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (! $schedule) {
            return;
        }

        $start = Carbon::createFromFormat('H:i:s', $schedule->start_time);
        $end = Carbon::createFromFormat('H:i:s', $schedule->end_time);
        $duration = $schedule->slot_duration ?: 30;

        // Fetch already booked slots
        $bookedSlots = AppointmentModel::where('doctor_id', $this->selectedDoctorId)
            ->where('appointment_date', $this->selectedDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $timeSlot = $start->format('H:i');
            if (! in_array($timeSlot, $bookedSlots)) {
                $this->availableSlots[] = $timeSlot;
            }
            $start->addMinutes($duration);
        }
    }

    public function selectSlot($time)
    {
        $this->selectedSlot = $time;
    }

    public function updateTotal()
    {
        $this->tax = $this->fee * 0.05;
        $this->total = $this->fee + $this->tax;
    }

    public function nextStep()
    {
        $this->validateStep();
        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function validateStep()
    {
        if ($this->step == 1) {
            $this->validate(['selectedClinicId' => 'required']);
        } elseif ($this->step == 2) {
            $this->validate(['selectedDoctorId' => 'required']);
        } elseif ($this->step == 3) {
            $this->validate([
                'selectedDate' => 'required|date|after_or_equal:today',
                'selectedSlot' => 'required',
            ]);
        }
    }

    public function bookAppointment()
    {
        $this->validate([
            'selectedClinicId' => 'required',
            'selectedDoctorId' => 'required',
            'selectedDate' => 'required',
            'selectedSlot' => 'required',
            'reason' => 'required',
        ]);

        // Find or create patient record for current user
        $patient = Patient::where('user_id', Auth::id())->first();

        if (! $patient) {
            // If patient record doesn't exist, we might need to create it or error.
            // For now, let's try to create a basic one or use the one we seeded.
            // In a real app, you'd have a profile completion step.
            $user = Auth::user();
            $patient = Patient::create([
                'clinic_id' => $this->selectedClinicId,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'gender' => 'other',
            ]);
        }

        $appointment = AppointmentModel::create([
            'clinic_id' => $this->selectedClinicId,
            'doctor_id' => $this->selectedDoctorId,
            'patient_id' => $patient->id,
            'appointment_date' => $this->selectedDate,
            'start_time' => $this->selectedSlot,
            'end_time' => Carbon::parse($this->selectedSlot)->addMinutes(30)->format('H:i'), // Default 30 min
            'status' => 'pending',
            'notes' => $this->reason.($this->notes ? "\n".$this->notes : ''),
        ]);

        session()->flash('message', 'Appointment booked successfully!');

        return redirect()->route('patient.dashboard'); // Or wherever relevant
    }

    public function render()
    {
        return view('livewire.patient.appointment')
            ->layout('components.layouts.app');
    }
}
