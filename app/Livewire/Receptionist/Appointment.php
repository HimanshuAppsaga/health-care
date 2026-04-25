<?php

namespace App\Livewire\Receptionist;

use App\Events\QueueUpdated;
use App\Models\Appointment as AppointmentModel;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $clinicId = Auth::user()->clinic_id;

        return [
            "echo:schedule-updates.{$clinicId},ScheduleUpdated" => 'generateSlots',
        ];
    }

    public function mount()
    {
        $this->clinics = Clinic::where('is_active', true)->get();
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

            // Auto-assign clinic and doctor if user is linked to a clinic
            if ($user->clinic_id) {
                $this->selectedClinicId = $user->clinic_id;
                $this->selectedClinic = Clinic::find($this->selectedClinicId);
                $this->doctors = Doctor::whereHas('user')->where('clinic_id', $this->selectedClinicId)->get();

                // Try to pick a doctor who has a schedule today
                $dayOfWeek = Carbon::today()->dayOfWeek;
                $this->selectedDoctorId = Doctor::where('clinic_id', $this->selectedClinicId)
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
    }

    public function updatedSelectedClinicId($value)
    {
        $this->selectedClinic = Clinic::find($value);
        $this->doctors = Doctor::whereHas('user')
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
        $this->selectedDate = Carbon::today()->format('Y-m-d'); // Force today
        $this->generateSlots();
    }

    public function generateSlots()
    {
        $this->availableSlots = [];
        if (! $this->selectedDoctorId || ! $this->selectedDate) {
            return;
        }

        // Check if doctor is on hold
        $doctor = Doctor::find($this->selectedDoctorId);
        if ($doctor && $doctor->is_on_hold) {
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)

        $schedules = DoctorSchedule::where('doctor_id', $this->selectedDoctorId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        foreach ($schedules as $schedule) {
            // Check if this session has reached its max patients limit
            $bookedCount = AppointmentModel::where('doctor_id', $this->selectedDoctorId)
                ->where('appointment_date', $this->selectedDate)
                ->whereTime('start_time', '>=', $schedule->start_time)
                ->whereTime('start_time', '<', $schedule->end_time)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            if ($schedule->max_patients > 0 && $bookedCount >= $schedule->max_patients) {
                continue; // Session is full
            }

            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $end = Carbon::createFromFormat('H:i:s', $schedule->end_time);
            $duration = $schedule->slot_duration ?: 30;

            // Fetch booked slots for this specific schedule to avoid overlapping
            $bookedSlotsForSchedule = AppointmentModel::where('doctor_id', $this->selectedDoctorId)
                ->where('appointment_date', $this->selectedDate)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereTime('start_time', '>=', $schedule->start_time)
                ->whereTime('start_time', '<', $schedule->end_time)
                ->pluck('start_time')
                ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
                ->toArray();

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
                'phone' => 'required|string|max:20',
                'reason' => 'required',
            ]);
        }
    }

    public function bookAppointment()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d'); // Force today
        $this->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'selectedSlot' => 'required',
        ]);

        // Double check slot availability
        if (! in_array($this->selectedSlot, $this->availableSlots)) {
            $this->generateSlots(); // Refresh slots
            if (! in_array($this->selectedSlot, $this->availableSlots)) {
                session()->flash('error', 'Selected slot is no longer available. Please pick another one.');

                return;
            }
        }

        try {
            return DB::transaction(function () {
                // Ensure clinic is selected
                if (! $this->selectedClinicId) {
                    $this->selectedClinicId = auth()->user()->clinic_id;
                }

                if (! $this->selectedClinicId) {
                    session()->flash('error', 'Unable to determine clinic. Please ensure your account is linked to a clinic.');

                    return;
                }

                // Ensure doctor is selected
                if (! $this->selectedDoctorId) {
                    $this->selectedDoctorId = Doctor::whereHas('user')->where('clinic_id', $this->selectedClinicId)->first()?->id;
                }

                if (! $this->selectedDoctorId) {
                    session()->flash('error', 'No doctor available to assign this appointment in your clinic.');

                    return;
                }

                // Find or create patient record by phone
                $patient = Patient::where('phone', $this->phone)->first();

                if (! $patient) {
                    $user = Auth::user();
                    $patient = Patient::create([
                        'clinic_id' => $this->selectedClinicId ?? 1,
                        'user_id' => $user?->id,
                        'name' => $this->name,
                        'email' => $user?->email ?? 'guest@example.com',
                        'phone' => $this->phone,
                        'dob' => '1990-01-01',
                        'gender' => 'other',
                    ]);
                } else {
                    // Update patient details if changed
                    $patient->update([
                        'name' => $this->name,
                        'user_id' => $patient->user_id ?? Auth::id(),
                    ]);
                }

                // Generate simple sequential token number for the day
                $tokenNumber = Queue::whereDate('created_at', Carbon::today())->count() + 1;

                $appointment = AppointmentModel::create([
                    'clinic_id' => $this->selectedClinicId,
                    'doctor_id' => $this->selectedDoctorId,
                    'patient_id' => $patient->id,
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'token' => $tokenNumber,
                    'appointment_date' => $this->selectedDate ?? Carbon::today()->format('Y-m-d'),
                    'start_time' => $this->selectedSlot ?? '09:00',
                    'end_time' => Carbon::parse($this->selectedSlot ?? '09:00')->addMinutes(30)->format('H:i'), // Default 30 min
                    'status' => 'pending',
                    'notes' => ($this->reason ?? 'Checkup').($this->notes ? "\n".$this->notes : ''),
                ]);

                Queue::create([
                    'appointment_id' => $appointment->id,
                    'token_number' => $tokenNumber,
                    'status' => 'waiting',
                ]);

                $this->generatedToken = $tokenNumber;

                Log::info('Appointment booked successfully', [
                    'appointment_id' => $appointment->id,
                    'token' => $tokenNumber,
                    'patient' => $this->name,
                ]);

                session()->flash('message', 'Appointment booked successfully! Your Token: '.$tokenNumber);

                $this->generateSlots(); // Refresh slots for next booking

                broadcast(new QueueUpdated($this->selectedClinicId, 'booked'))->toOthers();
            });
        } catch (\Exception $e) {
            Log::error('Failed to book appointment: '.$e->getMessage(), [
                'name' => $this->name,
                'phone' => $this->phone,
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Something went wrong while booking the appointment. Please try again.');
        }
    }

    public function getSelectedSessionProperty()
    {
        if (! $this->selectedSlot || ! $this->selectedDoctorId) {
            return null;
        }

        $slotTime = Carbon::parse($this->selectedSlot)->format('H:i:s');
        $dayOfWeek = Carbon::parse($this->selectedDate)->dayOfWeek;

        return DoctorSchedule::where('doctor_id', $this->selectedDoctorId)
            ->where('day_of_week', $dayOfWeek)
            ->whereTime('start_time', '<=', $slotTime)
            ->whereTime('end_time', '>', $slotTime)
            ->first();
    }

    public function render()
    {
        return view('livewire.receptionist.appointment')
            ->layout('components.layouts.app');
    }
}
