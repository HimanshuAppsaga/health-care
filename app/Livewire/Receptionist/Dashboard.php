<?php

namespace App\Livewire\Receptionist;

use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Queue;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Receptionist Dashboard | ClinicOS')]
class Dashboard extends Component
{
    public $lastStatus;

    public $lastTokenNumber;

    public $lastIsOnHoldStatus;

    public $selectedDoctorId;

    public $doctors = [];

    public function getListeners()
    {
        return [
            'echo:queue-updates.'.auth()->user()->clinic_id.',QueueUpdated' => '$refresh',
            'echo:schedule-updates.'.auth()->user()->clinic_id.',ScheduleUpdated' => '$refresh',
        ];
    }

    public function mount()
    {
        $clinicId = auth()->user()->clinic_id;
        $this->doctors = Doctor::with('user')->where('clinic_id', $clinicId)->get();

        if (empty($this->selectedDoctorId) && $this->doctors->isNotEmpty()) {
            $this->selectedDoctorId = $this->doctors->first()->id;
        }

        $today = Carbon::today();
        $nowServing = Queue::whereHas('appointment', function ($query) use ($today, $clinicId) {
            $query->where('clinic_id', $clinicId)
                ->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;

        $this->lastIsOnHoldStatus = Doctor::where('id', $this->selectedDoctorId)
            ->where('is_on_hold', true)
            ->exists();
    }

    public function updatedSelectedDoctorId($value)
    {
        $today = Carbon::today();
        $clinicId = auth()->user()->clinic_id;
        $nowServing = Queue::whereHas('appointment', function ($query) use ($today, $clinicId, $value) {
            $query->where('clinic_id', $clinicId)
                ->where('doctor_id', $value)
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;

        $this->lastIsOnHoldStatus = Doctor::where('id', $value)
            ->where('is_on_hold', true)
            ->exists();
    }

    public function callNextPatient()
    {
        $clinicId = auth()->user()->clinic_id;
        $isDoctorOnHold = Doctor::where('id', $this->selectedDoctorId)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();

        $current = Queue::whereHas('appointment', function ($query) use ($today, $clinicId) {
            $query->where('clinic_id', $clinicId)
                ->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', 'serving')->first();

        if ($current) {
            $current->update(['status' => 'completed']);
        }

        $next = Queue::whereHas('appointment', function ($query) use ($today, $clinicId) {
            $query->where('clinic_id', $clinicId)
                ->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->first();
        if ($next) {
            $next->update(['status' => 'serving', 'called_at' => now()]);
            broadcast(new QueueUpdated($clinicId, 'next'))->toOthers();
        }
    }

    public function markAsDone()
    {
        $clinicId = auth()->user()->clinic_id;
        $isDoctorOnHold = Doctor::where('id', $this->selectedDoctorId)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $current = Queue::whereHas('appointment', function ($query) use ($today, $clinicId) {
            $query->where('clinic_id', $clinicId)
                ->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })->whereIn('status', ['serving', 'hold'])->first();

        if ($current) {
            $current->update(['status' => 'completed']);
            if ($current->appointment) {
                $current->appointment->update(['status' => 'completed']);
            }
            broadcast(new QueueUpdated($clinicId, 'completed'))->toOthers();
        }
    }

    public function transferToken()
    {
        $clinicId = auth()->user()->clinic_id;
        $isDoctorOnHold = Doctor::where('id', $this->selectedDoctorId)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $current = Queue::whereHas('appointment', function ($query) use ($today, $clinicId) {
            $query->where('clinic_id', $clinicId)
                ->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', 'serving')->first();

        if ($current) {
            $currentTokenNum = (int) $current->token_number;
            $newTokenStr = (string) ($currentTokenNum + 6);

            // Shift the next 6 tokens down by 1
            $nextTokensToShift = Queue::whereHas('appointment', function ($query) use ($today, $clinicId) {
                $query->where('clinic_id', $clinicId)
                    ->where('doctor_id', $this->selectedDoctorId)
                    ->whereDate('appointment_date', $today);
            })
                ->where('status', 'waiting')
                ->whereRaw('CAST(token_number AS UNSIGNED) > ?', [$currentTokenNum])
                ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
                ->take(6)
                ->get();

            foreach ($nextTokensToShift as $q) {
                $num = (int) $q->token_number;
                $newNumStr = (string) ($num - 1);
                $q->update(['token_number' => $newNumStr]);
                if ($q->appointment) {
                    $q->appointment->update(['token' => $newNumStr]);
                }
            }

            // Update the transferred token
            $current->update([
                'token_number' => $newTokenStr,
                'status' => 'waiting',
            ]);

            if ($current->appointment) {
                $current->appointment->update(['token' => $newTokenStr]);
            }
            broadcast(new QueueUpdated($clinicId, 'transfer'))->toOthers();
        }
    }

    public function render()
    {
        $today = Carbon::today();
        $clinicId = auth()->user()->clinic_id;
        $doctorId = $this->selectedDoctorId;

        $totalAppointments = Appointment::where('clinic_id', $clinicId)
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->count();

        $checkedIn = Queue::whereHas('appointment', function ($query) use ($today, $clinicId, $doctorId) {
            $query->where('clinic_id', $clinicId)
                ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->count();

        $pendingArrivals = Appointment::where('clinic_id', $clinicId)
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->count();

        $completedToday = Appointment::where('clinic_id', $clinicId)
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $revenueToday = 0;
        if (class_exists(Payment::class)) {
            $revenueToday = Payment::whereHas('invoice.appointment', function ($query) use ($clinicId, $doctorId) {
                $query->where('clinic_id', $clinicId)
                    ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId));
            })
                ->whereDate('paid_at', $today)
                ->where('status', 'paid')
                ->sum('amount');
        } elseif (class_exists(Invoice::class)) {
            $revenueToday = Invoice::whereHas('appointment', function ($query) use ($clinicId, $doctorId) {
                $query->where('clinic_id', $clinicId)
                    ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId));
            })
                ->whereDate('issued_at', $today)
                ->where('status', 'paid')
                ->sum('total_amount');
        }

        $nowServing = Queue::with('appointment')
            ->whereHas('appointment', function ($query) use ($today, $clinicId, $doctorId) {
                $query->where('clinic_id', $clinicId)
                    ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                    ->whereDate('appointment_date', $today);
            })
            ->whereIn('status', ['serving', 'hold'])
            ->first();

        $currentStatus = $nowServing ? $nowServing->status : null;
        $currentToken = $nowServing ? $nowServing->token_number : null;

        $isDoctorOnHold = Doctor::where('id', $doctorId)
            ->where('is_on_hold', true)
            ->exists();

        // Detection Logic
        $shouldPlaySound = false;
        $reason = '';

        // 1. Check for Next Patient (Token change)
        if ($this->lastTokenNumber !== $currentToken) {
            if ($currentToken) {
                $shouldPlaySound = true;
                $reason = 'next';
            }
            $this->lastTokenNumber = $currentToken;
            $this->lastStatus = $currentStatus;
        }
        // 2. Check for Status change on same token
        elseif ($this->lastStatus !== $currentStatus) {
            if ($currentStatus === 'hold' || $currentStatus === 'serving') {
                $shouldPlaySound = true;
                $reason = $currentStatus === 'hold' ? 'hold' : 'continue';
            }
            $this->lastStatus = $currentStatus;
        }

        // 3. Fallback: Check for Doctor table Hold status (if no active queue or other changes)
        if (! $shouldPlaySound && $this->lastIsOnHoldStatus !== $isDoctorOnHold) {
            $shouldPlaySound = true;
            $reason = $isDoctorOnHold ? 'hold' : 'continue';
            $this->lastIsOnHoldStatus = $isDoctorOnHold;
        }

        if ($shouldPlaySound) {
            \Log::info("Sound: Dispatching $reason event via notify");
            $this->dispatch('notify', type: $reason);
        }

        $this->lastIsOnHoldStatus = $isDoctorOnHold; // Keep track regardless

        $nextTokens = Queue::with('appointment')
            ->whereHas('appointment', function ($query) use ($today, $clinicId, $doctorId) {
                $query->where('clinic_id', $clinicId)
                    ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                    ->whereDate('appointment_date', $today);
            })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->where('clinic_id', $clinicId)
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time', 'asc')
            ->get();

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($today, $clinicId, $doctorId) {
            $query->where('clinic_id', $clinicId)
                ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        $doctorSchedules = DoctorSchedule::with(['doctor.user'])
            ->whereHas('doctor', function ($query) {
                $query->where('clinic_id', auth()->user()->clinic_id);
            })
            ->where('day_of_week', $today->dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($schedule) use ($today) {
                // Count appointments for this doctor today that fall within this schedule's time range
                $schedule->booked_count = Appointment::where('doctor_id', $schedule->doctor_id)
                    ->whereDate('appointment_date', $today)
                    ->whereTime('start_time', '>=', $schedule->start_time)
                    ->whereTime('start_time', '<', $schedule->end_time)
                    ->count();

                return $schedule;
            });

        return view('livewire.receptionist.dashboard', [
            'totalAppointments' => $totalAppointments,
            'checkedIn' => $checkedIn,
            'pendingArrivals' => $pendingArrivals,
            'waitingCount' => $waitingCount,
            'revenueToday' => $revenueToday,
            'completedToday' => $completedToday,
            'nowServing' => $nowServing,
            'nextTokens' => $nextTokens,
            'todaysAppointments' => $todaysAppointments,
            'isDoctorOnHold' => $isDoctorOnHold,
            'doctorSchedules' => $doctorSchedules,
        ]);
    }
}
