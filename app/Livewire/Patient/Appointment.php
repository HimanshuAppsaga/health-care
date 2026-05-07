<?php

namespace App\Livewire\Patient;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Services\AppointmentBookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Book Appointment | ClinicOS')]
class Appointment extends Component
{
    protected AppointmentBookingService $bookingService;

    public function boot(AppointmentBookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public $step = 1;

    // Selection properties
    public $selectedClinicId;

    public $selectedDoctorId;

    public $selectedDate;

    public $selectedSlot;

    public $reason;

    public $notes;

    public $name;

    public $phone;

    public $generatedToken = null;

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

    public function getListeners()
    {
        return [
            'echo:schedule-updates.1,ScheduleUpdated' => 'generateSlots',
        ];
    }

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->generateSlots();

        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name;
            $this->phone = $user->phone;

            $patient = Patient::where('user_id', $user->id)->first();
            if ($patient) {
                $this->name = $patient->name;
                $this->phone = $patient->phone;
            }

            $this->doctors = Doctor::whereHas('user')->get();

            // Try to pick a doctor who has a schedule today
            $dayOfWeek = Carbon::today()->dayOfWeek;
            $this->selectedDoctorId = Doctor::whereHas('user')
                ->whereHas('schedules', function ($query) use ($dayOfWeek) {
                    $query->where('day_of_week', $dayOfWeek);
                })->first()?->id;

            // Fallback to first doctor if no one has a schedule today
            if (! $this->selectedDoctorId) {
                $this->selectedDoctorId = $this->doctors->first()?->id;
            }

            if ($this->selectedDoctorId) {
                $this->selectedDoctor = Doctor::with('user')->find($this->selectedDoctorId);
                $this->fee = $this->selectedDoctor->consultation_fee;
                $this->updateTotal();
                $this->generateSlots();
            }
        }
    }

    public function updatedSelectedClinicId($value)
    {
        $this->doctors = Doctor::whereHas('user')
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
        $this->selectedDate = Carbon::today()->format('Y-m-d'); // Force today
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

        $schedules = DoctorSchedule::where('doctor_id', $this->selectedDoctorId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // Note: Since we moved to a token-based system, we no longer store start_time in appointments.
        $bookedSlots = [];

        foreach ($schedules as $schedule) {
            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $end = Carbon::createFromFormat('H:i:s', $schedule->end_time);
            $duration = $schedule->slot_duration ?: 30;

            // If the schedule hasn't started yet today, don't allow bookings
            if ($this->selectedDate === Carbon::today()->format('Y-m-d') && now()->isBefore($start)) {
                continue;
            }

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $timeSlot = $start->format('H:i');

                // Filter out past slots if the date is today
                $isPast = false;
                if ($this->selectedDate === Carbon::today()->format('Y-m-d')) {
                    if ($start->copy()->addMinutes($duration)->isBefore(now())) {
                        $isPast = true;
                    }
                }

                if (! $isPast && ! in_array($timeSlot, $bookedSlots)) {
                    $this->availableSlots[] = $timeSlot;
                }
                $start->addMinutes($duration);
            }
        }

        // Sort slots by time
        sort($this->availableSlots);

        // Auto-select the first available slot
        if (! empty($this->availableSlots)) {
            if (empty($this->selectedSlot) || ! in_array($this->selectedSlot, $this->availableSlots)) {
                $this->selectedSlot = $this->availableSlots[0];
            }
        } else {
            $this->selectedSlot = null;
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
        } elseif ($this->step == 4) {
            $this->validate([
                'name' => 'required|string|max:191',
                'phone' => 'required|digits:10',
                'reason' => 'required',
            ]);
        }
    }

    public function bookAppointment()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d'); // Force today
        $this->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|digits:10',
        ]);

        if (! $this->selectedDoctorId) {
            $this->selectedDoctorId = 1;
        }

        $data = [
            'doctor_id' => $this->selectedDoctorId,
            'clinic_id' => $this->selectedClinicId,
            'name' => $this->name,
            'phone' => $this->phone,
            'appointment_date' => $this->selectedDate,
        ];

        $result = $this->bookingService->bookAppointment($data, Auth::user());

        if ($result['success']) {
            $this->generatedToken = $result['data']['token'];
            session()->flash('message', $result['message'].' Your Token: '.$result['data']['token']);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function render()
    {
        return view('livewire.patient.appointment');
    }
}
