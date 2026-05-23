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

    public $lastStatus;

    public $lastTokenNumber;

    public $lastIsOnHoldStatus;

    public $selectedDoctorId;

    public $doctors = [];

    public function getListeners()
    {
        $selectedDoctorId = $this->selectedDoctorId ?: session('receptionist_selected_doctor_id');
        if (! $selectedDoctorId) {
            $selectedDoctorId = Doctor::activeDoctor()->first()?->id;
        }

        $selectedDoctor = $selectedDoctorId ? Doctor::find($selectedDoctorId) : null;
        $apiKey = $selectedDoctor?->clinic?->api_key ?: '1';

        return [
            "echo:queue-updates.{$apiKey},QueueUpdated" => '$refresh',
            "echo:schedule-updates.{$apiKey},ScheduleUpdated" => '$refresh',
        ];
    }

    public function mount()
    {
        $this->doctors = Doctor::activeDoctor()->get();

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

        // Check if someone is already being served
        $today = Carbon::today();
        $isServing = Queue::whereHas('appointment', function ($query) use ($today) {
            $query->where('doctor_id', $this->selectedDoctorId)
                ->whereDate('appointment_date', $today);
        })->where('status', QueueStatus::SERVING)->exists();

        if ($isServing) {
            return;
        }

        $clinicId = auth()->user()->clinic_id ?? $doctor->clinic_id;

        $this->callNextTokenService->callNextToken($clinicId, $this->selectedDoctorId);
    }

    public function transferToken()
    {
        $doctor = Doctor::find($this->selectedDoctorId);
        if (! $doctor || $doctor->is_on_hold) {
            return;
        }

        $clinicId = auth()->user()->clinic_id ?? $doctor->clinic_id;
        $clinic = Clinic::find($clinicId);
        $count = $clinic->transfer_depth ?? 6;

        $result = $this->tokenTransferService->transferToken($clinicId, $this->selectedDoctorId, $count);

        if ($result['success']) {
            $this->dispatch('notify', type: 'success', message: $result['message']);
        } else {
            $this->dispatch('notify', type: 'error', message: $result['message']);
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
            ->where('status', 'pending')
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

        $appointmentService = app(AppointmentService::class);
        $todaysAppointments = $appointmentService->getTodayAppointments([
            'clinic_id' => auth()->user()->clinic_id,
        ]);

        $waitingCount = Queue::whereHas('appointment', function ($query) use ($today, $doctorId) {
            $query->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
                ->whereDate('appointment_date', $today);
        })
            ->where('status', 'waiting')
            ->count();

        $doctorsForSchedule = Doctor::activeDoctor()->with('user')
            ->when($doctorId, fn ($q) => $q->where('id', $doctorId))
            ->get();

        $doctorSchedules = collect();
        foreach ($doctorsForSchedule as $doc) {
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
        $doctorSchedules = $doctorSchedules->sortBy('start_time');

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
