<?php

namespace App\Livewire\Receptionist;

use App\Models\Doctor;
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

    public $bookingAllowed = true;

    public $bookingMessage = '';

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
            $dayNames = [
                0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday',
                4 => 'thursday', 5 => 'friday', 6 => 'saturday',
            ];
            $dayName = $dayNames[$dayOfWeek];

            $this->selectedDoctorId = session('receptionist_selected_doctor_id');

            if (!$this->selectedDoctorId) {
                $this->selectedDoctorId = Doctor::whereHas('user')
                    ->where('working_hours->' . $dayName, '!=', 'Closed')
                    ->first()?->id;

                // Fallback to first doctor if no one has a schedule today
                if (!$this->selectedDoctorId) {
                    $this->selectedDoctorId = $this->doctors->first()?->id;
                }
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
        $this->generateSlots();
    }

    public function generateSlots()
    {
        $this->availableSlots = [];
        if (! $this->selectedDoctorId || ! $this->selectedDate) {
            return;
        }

        // Note: Doctor "on hold" check removed from slot generation.
        // Doctors can still be booked even if they are temporarily pausing their current session.

        $date = Carbon::parse($this->selectedDate);
        $doctor = Doctor::find($this->selectedDoctorId);

        if (!$doctor) {
            return;
        }

        // Check booking status using reusable logic
        $status = $this->bookingService->checkBookingStatus($doctor, $date);
        $this->bookingAllowed = $status['allowed'];
        $this->bookingMessage = $status['message'];

        if (!$this->bookingAllowed) {
            $this->availableSlots = [];
            $this->selectedSlot = null;
            return;
        }

        $dayOfWeek = $date->dayOfWeek;
        $schedules = $doctor->getScheduleForDay($dayOfWeek);

        foreach ($schedules as $schedule) {

            $start = Carbon::createFromFormat('H:i:s', $schedule['start_time']);
            $end = Carbon::createFromFormat('H:i:s', $schedule['end_time']);
            $duration = $schedule['slot_duration'] ?? 15;

            // If the schedule hasn't started yet today, don't allow bookings
            if ($this->selectedDate === Carbon::today()->format('Y-m-d') && now()->isBefore($start)) {
                continue;
            }

            // Note: Since we moved to a token-based system, we no longer store start_time in appointments.
            // We can show all slots available in the doctor's schedule, or limit by max_patients if needed.
            $bookedSlotsForSchedule = [];

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $timeSlot = $start->format('H:i');

                // Filter out past slots if the date is today
                $isPast = false;
                if ($this->selectedDate === Carbon::today()->format('Y-m-d')) {
                    if ($start->copy()->addMinutes($duration)->isBefore(now())) {
                        $isPast = true;
                    }
                }

                if (! $isPast && ! in_array($timeSlot, $bookedSlotsForSchedule)) {
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
                'phone' => [
                    'required',
                    'digits:10',
                    function ($attribute, $value, $fail) {
                        $exists = \App\Models\Appointment::where('phone', $value)
                            ->where('created_at', '>=', now()->subHours(24))
                            ->exists();

                        if ($exists) {
                            $fail('appointment is already booked With This Number.');
                        }
                    },
                ],
                'reason' => 'required',
            ]);
        }
    }

    public function bookAppointment()
    {
        $this->validate([
            'name' => 'required|string|max:191',
            'phone' => [
                'required',
                'digits:10',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Appointment::where('phone', $value)
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if ($exists) {
                        $fail('appointment is already booked With This Number.');
                    }
                },
            ],
        ]);

        if (! $this->selectedDoctorId) {
            $this->selectedDoctorId = Doctor::whereHas('user')->first()?->id;
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

    public function getSelectedSessionProperty()
    {
        if (! $this->selectedSlot || ! $this->selectedDoctorId || ! $this->selectedDate) {
            return null;
        }

        $date = Carbon::parse($this->selectedDate);
        $dayOfWeek = $date->dayOfWeek;
        $dayNames = [
            0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday',
            4 => 'thursday', 5 => 'friday', 6 => 'saturday',
        ];
        $dayName = $dayNames[$dayOfWeek];

        $doctor = Doctor::find($this->selectedDoctorId);
        if (! $doctor) {
            return null;
        }

        $sessions = $doctor->getScheduleForDay($dayOfWeek);
        $slotTime = Carbon::createFromFormat('H:i', $this->selectedSlot)->format('H:i:s');

        foreach ($sessions as $session) {
            if ($session['start_time'] <= $slotTime && $session['end_time'] > $slotTime) {
                return (object) $session;
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.receptionist.appointment');
    }
}
