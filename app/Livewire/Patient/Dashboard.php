<?php

namespace App\Livewire\Patient;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\Prescription;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Patient Dashboard | HealthSync Pro')]
class Dashboard extends Component
{
    public $patient;

    public $nextAppointment;

    public $upcomingAppointments;

    public $recentPrescriptions;

    public $notifications;

    // Mock data for vitals as they don't exist in schema yet
    public $vitals = [
        'heart_rate' => 72,
        'weight' => 168,
        'sleep_avg' => 7.5,
        'heart_rate_change' => -4,
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->patient = $user->patient;

        if (! $this->patient) {
            // Handle case where user is not a patient
            // For now, we'll just use the user object if patient record is missing
            // but in a real app, we'd redirect or show an error.
        }

        $this->loadData();
    }

    public function loadData()
    {
        $patientId = $this->patient ? $this->patient->id : null;

        if ($patientId) {
            // Next Appointment
            $this->nextAppointment = Appointment::where('patient_id', $patientId)
                ->where('appointment_date', '>=', now()->toDateString())
                ->where('status', 'pending')
                ->with('doctor.user')
                ->orderBy('appointment_date')
                ->orderByRaw('CAST(token AS UNSIGNED) ASC')
                ->first();

            // Upcoming Appointments (excluding the next one)
            $this->upcomingAppointments = Appointment::where('patient_id', $patientId)
                ->where('appointment_date', '>=', now()->toDateString())
                ->where('status', 'pending')
                ->when($this->nextAppointment, function ($query) {
                    $query->where('id', '!=', $this->nextAppointment->id);
                })
                ->with('doctor.user')
                ->orderBy('appointment_date')
                ->orderByRaw('CAST(token AS UNSIGNED) ASC')
                ->limit(3)
                ->get();

            // Recent Prescriptions
            $this->recentPrescriptions = Prescription::where('patient_id', $patientId)
                ->with(['doctor.user', 'items'])
                ->latest()
                ->limit(3)
                ->get();

            // Notifications
            $this->notifications = Notification::where('user_id', Auth::id())
                ->latest()
                ->limit(5)
                ->get();
        } else {
            $this->upcomingAppointments = collect();
            $this->recentPrescriptions = collect();
            $this->notifications = collect();
        }
    }

    public function render()
    {
        return view('livewire.patient.dashboard');
    }
}
