<?php

namespace App\Livewire\Receptionist;

use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Services\CallNextTokenService;
use App\Services\CurrentTokenService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Receptionist Dashboard | ClinicOS')]
class Dashboard extends Component
{
    protected $currentTokenService;

    protected $callNextTokenService;

    public function boot(CurrentTokenService $currentTokenService, CallNextTokenService $callNextTokenService)
    {
        $this->currentTokenService = $currentTokenService;
        $this->callNextTokenService = $callNextTokenService;
    }

    public $lastStatus;

    public $lastTokenNumber;

    public $lastIsOnHoldStatus;

    public $selectedDoctorId;

    public $doctors = [];

    public function getListeners()
    {
        return [
            'echo:queue-updates.1,QueueUpdated' => '$refresh',
            'echo:schedule-updates.1,ScheduleUpdated' => '$refresh',
        ];
    }

    public function mount()
    {
        $this->doctors = Doctor::whereHas('user')->get();

        $this->selectedDoctorId = session('receptionist_selected_doctor_id');

        if ((empty($this->selectedDoctorId) || ! $this->doctors->contains('id', $this->selectedDoctorId)) && $this->doctors->isNotEmpty()) {
            $this->selectedDoctorId = $this->doctors->first()->id;
            session(['receptionist_selected_doctor_id' => $this->selectedDoctorId]);
        }

        $result = $this->currentTokenService->getCurrentToken(auth()->user()->clinic_id, $this->selectedDoctorId);
        $nowServing = $result['data']['current_token'];

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;

        $this->lastIsOnHoldStatus = Doctor::where('id', $this->selectedDoctorId)
            ->where('is_on_hold', true)
            ->exists();
    }

    public function updatedSelectedDoctorId($value)
    {
        session(['receptionist_selected_doctor_id' => $value]);

        $result = $this->currentTokenService->getCurrentToken(auth()->user()->clinic_id, $value);
        $nowServing = $result['data']['current_token'];

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;

        $this->lastIsOnHoldStatus = Doctor::where('id', $value)
            ->where('is_on_hold', true)
            ->exists();
    }

    public function callNextPatient()
    {
        $doctor = Doctor::find($this->selectedDoctorId);
        if (! $doctor) {
            return;
        }

        if ($doctor->is_on_hold) {
            return;
        }

        $clinicId = auth()->user()->clinic_id ?? $doctor->clinic_id;

        $this->callNextTokenService->callNextToken($clinicId, $this->selectedDoctorId);
    }

    public function transferToken()
    {
        $isDoctorOnHold = Doctor::where('id', $this->selectedDoctorId)->where('is_on_hold', true)->exists();
        if ($isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $current = Queue::whereHas('appointment', function ($query) use ($today) {
            $query->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', 'serving')->first();

        if ($current) {
            $clinic = $current->appointment->clinic;
            $depth = $clinic->transfer_depth ?? 6;
            $currentTokenNum = (int) $current->token_number;
            $newTokenStr = (string) ($currentTokenNum + $depth);

            // Shift the next X tokens down by 1
            $nextTokensToShift = Queue::whereHas('appointment', function ($query) use ($today) {
                $query->where('doctor_id', $this->selectedDoctorId)
                    ->whereDate('appointment_date', $today);
            })
                ->where('status', 'waiting')
                ->whereRaw('CAST(token_number AS UNSIGNED) > ?', [$currentTokenNum])
                ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
                ->take($depth)
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
                $current->appointment->update(['status' => 'confirmed', 'token' => $newTokenStr]);
            }

            $clinicId = auth()->user()->clinic_id ?? $current->appointment->clinic_id;
            broadcast(new QueueUpdated($clinicId, 'transfer'))->toOthers();
        }
    }

    public function render()
    {
        $today = Carbon::today();
        $doctorId = $this->selectedDoctorId;

        $totalAppointments = Appointment::when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->count();

        $checkedIn = Queue::whereHas('appointment', function ($query) use ($today, $doctorId) {
            $query->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->count();

        $pendingArrivals = Appointment::when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->where('status', 'confirmed')
            ->count();

        $completedToday = Appointment::when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $revenueToday = 0;

        $result = $this->currentTokenService->getCurrentToken(auth()->user()->clinic_id, $doctorId);
        $nowServing = $result['data']['current_token'];

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
            if ($currentStatus?->value === 'hold' || $currentStatus?->value === 'serving') {
                $shouldPlaySound = true;
                $reason = $currentStatus?->value === 'hold' ? 'hold' : 'continue';
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
            ->whereHas('appointment', function ($query) use ($today, $doctorId) {
                $query->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                    ->whereDate('appointment_date', $today);
            })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->whereDate('appointment_date', $today)
            ->orderByRaw('CAST(token AS UNSIGNED) ASC')
            ->get();

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($today, $doctorId) {
            $query->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        $doctorSchedules = DoctorSchedule::with(['doctor.user'])
            ->whereHas('doctor', function ($query) use ($doctorId) {
                $query->when($doctorId, fn ($q) => $q->where('id', $doctorId));
            })
            ->where('day_of_week', $today->dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($schedule) use ($today) {
                // Since appointments no longer have times, we can't accurately split them by session.
                $schedule->booked_count = Appointment::where('doctor_id', $schedule->doctor_id)
                    ->whereDate('appointment_date', $today)
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
            'transferDepth' => $nowServing?->appointment?->clinic?->transfer_depth ?? Clinic::first()?->transfer_depth ?? 6,
        ]);
    }
}
