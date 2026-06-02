<?php

namespace App\Livewire\Receptionist;

use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Queue;
use App\Services\AppointmentService;
use App\Services\CallNextTokenService;
use App\Services\CurrentTokenService;
use App\Services\TokenTransferService;
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

    public array $lastTokenNumbers = [];

    public array $lastStatuses = [];

    public array $lastIsOnHoldStatuses = [];

    public $doctors = [];

    public function getListeners()
    {
        $apiKey = $this->doctors->first()?->clinic?->api_key ?: '1';

        return [
            "echo:queue-updates.{$apiKey},QueueUpdated" => '$refresh',
            "echo:schedule-updates.{$apiKey},ScheduleUpdated" => '$refresh',
        ];
    }

    public function mount()
    {
        $this->doctors = Doctor::activeDoctor()->get();

        foreach ($this->doctors as $doctor) {
            $result = $this->currentTokenService->getCurrentToken(auth()->user()->clinic_id, $doctor->id);
            $nowServing = $result['data']['current_token'];

            $this->lastTokenNumbers[$doctor->id] = $nowServing ? $nowServing->token_number : null;
            $this->lastStatuses[$doctor->id] = $nowServing ? $nowServing->status?->value : null;
            $this->lastIsOnHoldStatuses[$doctor->id] = $doctor->is_on_hold;
        }
    }

    public function callNextPatient($doctorId)
    {
        $doctor = Doctor::find($doctorId);
        if (! $doctor) {
            return;
        }

        if ($doctor->is_on_hold) {
            return;
        }

        // Check if someone is already being served
        $today = Carbon::today();
        $isServing = Queue::whereHas('appointment', function ($query) use ($today, $doctorId) {
            $query->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', QueueStatus::SERVING)->exists();

        if ($isServing) {
            return;
        }

        $clinicId = auth()->user()->clinic_id ?? $doctor->clinic_id;

        $this->callNextTokenService->callNextToken($clinicId, $doctorId);
    }

    public function transferToken($doctorId)
    {
        $doctor = Doctor::find($doctorId);
        if (! $doctor || $doctor->is_on_hold) {
            return;
        }

        $clinicId = auth()->user()->clinic_id ?? $doctor->clinic_id;
        $clinic = Clinic::find($clinicId);
        $count = $clinic->transfer_depth ?? 6;

        $result = $this->tokenTransferService->transferToken($clinicId, $doctorId, $count);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('notify', type: 'error', message: $result['message']);
        }
    }

    public function render()
    {
        $today = Carbon::today();

        // Global stats for receptionist across all doctors
        $totalAppointments = Appointment::whereDate('appointment_date', $today)->count();

        $checkedIn = Queue::whereHas('appointment', function ($query) use ($today) {
            $query->whereDate('appointment_date', $today);
        })
            ->whereIn('status', ['waiting', 'serving', 'completed'])
            ->count();

        $pendingArrivals = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->count();

        $completedToday = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $revenueToday = 0;

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($today) {
            $query->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        $queuesData = [];
        $shouldPlaySound = false;
        $reason = '';

        foreach ($this->doctors as $doctor) {
            $result = $this->currentTokenService->getCurrentToken(auth()->user()->clinic_id, $doctor->id);
            $nowServing = $result['data']['current_token'];

            $currentStatus = $nowServing ? $nowServing->status?->value : null;
            $currentToken = $nowServing ? $nowServing->token_number : null;

            $isDoctorOnHold = $doctor->is_on_hold;

            // Detection Logic
            if (($this->lastTokenNumbers[$doctor->id] ?? null) !== $currentToken) {
                if ($currentToken) {
                    $shouldPlaySound = true;
                    $reason = 'next';
                }
                $this->lastTokenNumbers[$doctor->id] = $currentToken;
                $this->lastStatuses[$doctor->id] = $currentStatus;
            } elseif (($this->lastStatuses[$doctor->id] ?? null) !== $currentStatus) {
                if ($currentStatus === 'hold' || $currentStatus === 'serving') {
                    $shouldPlaySound = true;
                    $reason = $currentStatus === 'hold' ? 'hold' : 'continue';
                }
                $this->lastStatuses[$doctor->id] = $currentStatus;
            }

            if (! $shouldPlaySound && ($this->lastIsOnHoldStatuses[$doctor->id] ?? null) !== $isDoctorOnHold) {
                $shouldPlaySound = true;
                $reason = $isDoctorOnHold ? 'hold' : 'continue';
                $this->lastIsOnHoldStatuses[$doctor->id] = $isDoctorOnHold;
            }
            $this->lastIsOnHoldStatuses[$doctor->id] = $isDoctorOnHold;

            $nextTokens = Queue::with('appointment')
                ->whereHas('appointment', function ($query) use ($today, $doctor) {
                    $query->where('doctor_id', $doctor->id)
                        ->whereDate('appointment_date', $today);
                })
                ->where('status', 'waiting')
                ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
                ->take(3)
                ->get();

            $queuesData[$doctor->id] = [
                'doctor' => $doctor,
                'nowServing' => $nowServing,
                'nextTokens' => $nextTokens,
                'isDoctorOnHold' => $isDoctorOnHold,
                'transferDepth' => $nowServing?->appointment?->clinic?->transfer_depth ?? Clinic::first()?->transfer_depth ?? 6,
            ];
        }

        if ($shouldPlaySound) {
            \Log::info("Sound: Dispatching $reason event via notify");
            $this->dispatch('notify', type: $reason);
        }

        $appointmentService = app(AppointmentService::class);
        $todaysAppointments = $appointmentService->getTodayAppointments([
            'clinic_id' => auth()->user()->clinic_id,
        ]);

        $doctorSchedules = collect();
        foreach ($this->doctors as $doc) {
            $sessions = $doc->getScheduleForDay($today->dayOfWeek);
            foreach ($sessions as $session) {
                $sObj = (object) [
                    'doctor_id' => $doc->id,
                    'doctor' => $doc,
                    'start_time' => $session['start_time'],
                    'end_time' => $session['end_time'],
                ];
                $sObj->booked_count = Appointment::where('doctor_id', $doc->id)
                    ->whereDate('appointment_date', $today)
                    ->count();
                $doctorSchedules->push($sObj);
            }
        }
        $doctorSchedules = $doctorSchedules->sortBy('start_time')->groupBy('doctor_id');

        return view('livewire.receptionist.dashboard', [
            'totalAppointments' => $totalAppointments,
            'checkedIn' => $checkedIn,
            'pendingArrivals' => $pendingArrivals,
            'waitingCount' => $waitingCount,
            'revenueToday' => $revenueToday,
            'completedToday' => $completedToday,
            'queuesData' => $queuesData,
            'todaysAppointments' => $todaysAppointments,
            'doctorSchedules' => $doctorSchedules,
        ]);
    }
}
