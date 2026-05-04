<?php

namespace App\Livewire\Doctor;

use App\Enums\QueueStatus;
use App\Events\QueueUpdated;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\Queue;
use App\Services\CallNextTokenService;
use App\Services\CurrentTokenService;
use App\Services\QueueService;
use App\Services\TokenTransferService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Doctor Dashboard | ClinicOS')]
class Dashboard extends Component
{
    protected $currentTokenService;

    protected $callNextTokenService;

    protected $tokenTransferService;

    public function boot(
        CurrentTokenService $currentTokenService,
        CallNextTokenService $callNextTokenService,
        TokenTransferService $tokenTransferService
    ) {
        $this->currentTokenService = $currentTokenService;
        $this->callNextTokenService = $callNextTokenService;
        $this->tokenTransferService = $tokenTransferService;
    }

    public bool $isDoctorOnHold = false;

    public $lastTokenNumber;

    public $lastStatus;

    public function getListeners()
    {
        $clinicId = 1;

        return [
            "echo:queue-updates.{$clinicId},QueueUpdated" => '$refresh',
            "echo:schedule-updates.{$clinicId},ScheduleUpdated" => '$refresh',
        ];
    }

    public function mount()
    {
        $user = auth()->user();
        $doctor = $user->ensureDoctorProfileExists();

        $this->isDoctorOnHold = $doctor ? (bool) $doctor->is_on_hold : false;

        $doctorId = $doctor->id ?? 0;
        $result = $this->currentTokenService->getCurrentToken($doctor->clinic_id ?? null, $doctorId);
        $nowServing = $result['data']['current_token'];

        $this->lastTokenNumber = $nowServing ? $nowServing->token_number : null;
        $this->lastStatus = $nowServing ? $nowServing->status : null;
    }

    public function callNextPatient()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $doctor = auth()->user()->doctor;
        $clinicId = $doctor->clinic_id ?? 1;
        $doctorId = $doctor->id ?? 0;

        $this->callNextTokenService->callNextToken($clinicId, $doctorId);
    }

    public function markAsDone()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $current = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })->whereIn('status', [QueueStatus::SERVING->value, QueueStatus::HOLD->value])->first();

        if ($current) {
            app(QueueService::class)->markAsDone($current->appointment_id, 1); // Clinic ID is hardcoded as 1 in existing code, but should probably be dynamic
        }
    }

    public function toggleHold()
    {
        $doctor = auth()->user()->doctor;
        if ($doctor) {
            $this->isDoctorOnHold = ! $this->isDoctorOnHold;
            $doctor->update(['is_on_hold' => $this->isDoctorOnHold]);

            // If toggling hold while serving, also update the queue status for better reflection
            $today = Carbon::today();
            $doctorId = $doctor->id;

            $current = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
                $query->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today);
            })
                ->whereIn('status', [QueueStatus::SERVING->value, QueueStatus::HOLD->value])
                ->first();

            if ($current) {
                if ($this->isDoctorOnHold) {
                    app(QueueService::class)->hold($current->appointment_id, 1);
                } else {
                    $current->update(['status' => QueueStatus::SERVING->value]);
                    broadcast(new QueueUpdated(1, 'continue'))->toOthers();
                }
            } else {
                broadcast(new QueueUpdated(1, $this->isDoctorOnHold ? 'hold' : 'continue'))->toOthers();
            }
        }
    }

    public function transferToken()
    {
        if ($this->isDoctorOnHold) {
            return;
        }

        $doctor = auth()->user()->doctor;
        if (! $doctor) {
            return;
        }

        $clinicId = $doctor->clinic_id ?? 1;
        $doctorId = $doctor->id;
        $depth = $doctor->clinic->transfer_depth ?? 6;

        $result = $this->tokenTransferService->transferToken($clinicId, $doctorId, $depth);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('notify', type: 'error', message: $result['message']);
        }
    }

    public function render()
    {
        $today = Carbon::today();
        $doctor = auth()->user()->doctor;
        $doctorId = $doctor->id ?? 0;

        $totalAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();

        $checkedIn = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->count();

        $pendingArrivals = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->count();

        $completedToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $revenueToday = 0;

        $result = $this->currentTokenService->getCurrentToken($doctor->clinic_id ?? null, $doctorId);
        $nowServing = $result['data']['current_token'];

        $currentStatus = $nowServing ? $nowServing->status : null;
        $currentToken = $nowServing ? $nowServing->token_number : null;

        // Detection Logic for Notifications
        $shouldNotify = false;
        $reason = '';

        if ($this->lastTokenNumber !== $currentToken) {
            if ($currentToken) {
                $shouldNotify = true;
                $reason = 'next';
            }
            $this->lastTokenNumber = $currentToken;
            $this->lastStatus = $currentStatus;
        } elseif ($this->lastStatus !== $currentStatus) {
            if ($currentStatus?->value === 'hold' || $currentStatus?->value === 'serving') {
                $shouldNotify = true;
                $reason = $currentStatus?->value === 'hold' ? 'hold' : 'continue';
            }
            $this->lastStatus = $currentStatus;
        }

        if ($shouldNotify) {
            $this->dispatch('notify', type: $reason);
        }

        $nextTokens = Queue::with('appointment')
            ->whereHas('appointment', function ($query) use ($doctorId, $today) {
                $query->where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today);
            })
            ->where('status', 'waiting')
            ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
            ->take(3)
            ->get();

        $todaysAppointments = Appointment::with(['doctor.user'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->orderByRaw('CAST(token AS UNSIGNED) ASC')
            ->get();

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($doctorId, $today) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        $isDoctorOnHold = $this->isDoctorOnHold;

        $doctorSchedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $today->dayOfWeek)
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($schedule) use ($today) {
                $schedule->booked_count = Appointment::where('doctor_id', $schedule->doctor_id)
                    ->whereDate('appointment_date', $today)
                    ->count();

                return $schedule;
            });

        return view('livewire.doctor.dashboard', [
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
            'transferDepth' => auth()->user()->doctor?->clinic?->transfer_depth ?? 6,
        ]);
    }
}
