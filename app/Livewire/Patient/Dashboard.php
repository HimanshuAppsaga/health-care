<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Queue;
use App\Services\CurrentTokenService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Queue Dashboard | Clinic Saga')]
class Dashboard extends Component
{
    protected $currentTokenService;

    public function boot(CurrentTokenService $currentTokenService)
    {
        $this->currentTokenService = $currentTokenService;
    }

    public function getListeners()
    {
        $user = auth()->user();
        $patient = $user ? $user->patient : null;
        $today = Carbon::today();

        $appointment = null;
        if ($patient) {
            $appointment = Appointment::where('patient_id', $patient->id)
                ->whereDate('appointment_date', $today)
                ->first();

            if (! $appointment) {
                $appointment = Appointment::where('patient_id', $patient->id)
                    ->latest('appointment_date')
                    ->first();
            }
        }

        $clinicId = $appointment ? $appointment->clinic_id : ($patient->clinic_id ?? null);
        if (! $clinicId) {
            $firstClinic = Clinic::first();
            $clinicId = $firstClinic ? $firstClinic->id : null;
        }

        $apiKey = $clinicId ? Clinic::where('id', $clinicId)->value('api_key') : '1';
        $apiKey = $apiKey ?: '1';

        return [
            "echo:queue-updates.{$apiKey},QueueUpdated" => '$refresh',
            "echo:schedule-updates.{$apiKey},ScheduleUpdated" => '$refresh',
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $patient = $user->patient;
        $today = Carbon::today();

        // 1. Try to find the patient's appointment for today
        $appointment = null;
        if ($patient) {
            $appointment = Appointment::where('patient_id', $patient->id)
                ->whereDate('appointment_date', $today)
                ->first();

            // 2. If no appointment today, get the latest appointment
            if (! $appointment) {
                $appointment = Appointment::where('patient_id', $patient->id)
                    ->latest('appointment_date')
                    ->first();
            }
        }

        // 3. Resolve Doctor ID & Clinic ID
        $doctorId = $appointment ? $appointment->doctor_id : null;
        $clinicId = $appointment ? $appointment->clinic_id : ($patient->clinic_id ?? null);

        // Fallbacks if doctor or clinic are still not found
        if (! $doctorId) {
            $firstDoctor = Doctor::activeDoctor()->first();
            $doctorId = $firstDoctor ? $firstDoctor->id : null;
        }
        if (! $clinicId) {
            $firstClinic = Clinic::first();
            $clinicId = $firstClinic ? $firstClinic->id : null;
        }

        // 4. Fetch the current serving token for this doctor
        $nowServing = null;
        $nextTokens = collect();
        $isDoctorOnHold = false;

        if ($doctorId && $clinicId) {
            $result = $this->currentTokenService->getCurrentToken($clinicId, $doctorId);
            $nowServing = $result['success'] ? $result['data']['current_token'] : null;

            $nextTokens = Queue::with('appointment')
                ->whereHas('appointment', function ($query) use ($doctorId, $today) {
                    $query->where('doctor_id', $doctorId)
                        ->whereDate('appointment_date', $today);
                })
                ->where('status', 'waiting')
                ->orderByRaw('CAST(token_number AS UNSIGNED) ASC')
                ->take(3)
                ->get();

            $isDoctorOnHold = Doctor::where('id', $doctorId)
                ->where('is_on_hold', true)
                ->exists();
        }

        return view('livewire.patient.dashboard', [
            'nowServing' => $nowServing,
            'nextTokens' => $nextTokens,
            'isDoctorOnHold' => $isDoctorOnHold,
            'appointment' => $appointment,
        ]);
    }
}
