<?php

namespace App\Livewire\ClinicAdmin;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];
    public $recentAppointments;
    public $liveQueue = [];
    public array $chartData = [];
    public array $revenueMix = [];
    public int $efficiencyScore = 0;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $clinicId = Auth::user()->clinic_id;

        // Stats Cards
        $this->stats = [
            'total_patients' => Patient::where('clinic_id', $clinicId)->count(),
            'today_appointments' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', Carbon::today())
                ->count(),
            'revenue_today' => Invoice::where('clinic_id', $clinicId)
                ->whereDate('issued_at', Carbon::today())
                ->where('status', 'paid')
                ->sum('total_amount'),
            'active_doctors' => Doctor::where('clinic_id', $clinicId)->count(),
            'doctor_total' => Doctor::where('clinic_id', $clinicId)->count(), // For "14/18" style
        ];

        // Recent Appointments
        $this->recentAppointments = Appointment::where('clinic_id', $clinicId)
            ->with(['patient', 'doctor.user'])
            ->latest()
            ->take(4)
            ->get();

        // Live Queue
        $serving = Queue::whereHas('appointment', fn($q) => $q->where('clinic_id', $clinicId))
            ->where('status', 'serving')
            ->with('appointment.doctor.user')
            ->first();

        $next = Queue::whereHas('appointment', fn($q) => $q->where('clinic_id', $clinicId))
            ->where('status', 'waiting')
            ->orderBy('token_number')
            ->first();

        $waitingCount = Queue::whereHas('appointment', fn($q) => $q->where('clinic_id', $clinicId))
            ->where('status', 'waiting')
            ->count();

        $this->liveQueue = [
            'serving' => $serving,
            'next' => $next,
            'waiting_count' => $waitingCount,
        ];

        // Chart Data (Last 7 days)
        $days = [];
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = $date->format('D');
            $counts[] = Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', $date)
                ->count();
        }
        $this->chartData = [
            'labels' => $days,
            'data' => $counts,
        ];

        // Revenue Mix (Mocked for now)
        $this->revenueMix = [
            ['label' => 'Consultations', 'amount' => 4200, 'percentage' => 75],
            ['label' => 'Lab Tests', 'amount' => 2800, 'percentage' => 50],
            ['label' => 'Pharmacy', 'amount' => 1240, 'percentage' => 25],
        ];

        $this->efficiencyScore = 94; // Mocked
    }

    public function render()
    {
        return view('livewire.clinicAdmin.dashboard')
            ->layout('components.layouts.app');
    }
}
