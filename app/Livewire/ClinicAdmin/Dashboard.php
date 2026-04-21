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
    public float $lastMonthEfficiency = 0;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $clinicId = Auth::user()->clinic_id;

        // Growth Calculations
        $patientsTotal = Patient::where('clinic_id', $clinicId)->count();
        $patientsLastWeek = Patient::where('clinic_id', $clinicId)
            ->where('created_at', '<', Carbon::now()->subWeek())
            ->count();
        $patientGrowth = $patientsLastWeek > 0 
            ? round((($patientsTotal - $patientsLastWeek) / $patientsLastWeek) * 100, 1) 
            : 0;

        $revenueToday = Invoice::where('clinic_id', $clinicId)
            ->whereDate('issued_at', Carbon::today())
            ->where('status', 'paid')
            ->sum('total_amount');
        $revenueYesterday = Invoice::where('clinic_id', $clinicId)
            ->whereDate('issued_at', Carbon::yesterday())
            ->where('status', 'paid')
            ->sum('total_amount');
        $revenueGrowth = $revenueYesterday > 0 
            ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100, 1) 
            : 0;

        // Stats Cards
        $this->stats = [
            'total_patients' => $patientsTotal,
            'patient_growth' => $patientGrowth,
            'today_appointments' => Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', Carbon::today())
                ->count(),
            'revenue_today' => $revenueToday,
            'revenue_growth' => $revenueGrowth,
            'active_doctors' => Doctor::where('clinic_id', $clinicId)->count(),
            'doctor_total' => Doctor::where('clinic_id', $clinicId)->count(),
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

        // Dynamic Revenue Mix
        $totalRevenue = Invoice::where('clinic_id', $clinicId)->where('status', 'paid')->sum('total_amount') ?: 1;
        $consultationRevenue = Invoice::where('clinic_id', $clinicId)
            ->where('status', 'paid')
            ->whereNotNull('appointment_id')
            ->sum('total_amount');

        $this->revenueMix = [
            [
                'label' => 'Consultations', 
                'amount' => $consultationRevenue, 
                'percentage' => round(($consultationRevenue / $totalRevenue) * 100)
            ],
            [
                'label' => 'Lab Tests', 
                'amount' => 0, 
                'percentage' => 0
            ],
            [
                'label' => 'Pharmacy', 
                'amount' => 0, 
                'percentage' => 0
            ],
        ];

        // Current Month Efficiency
        $totalThisMonth = Appointment::where('clinic_id', $clinicId)
            ->whereMonth('appointment_date', Carbon::now()->month)
            ->count();
        $completedThisMonth = Appointment::where('clinic_id', $clinicId)
            ->whereMonth('appointment_date', Carbon::now()->month)
            ->where('status', 'completed')
            ->count();
        $this->efficiencyScore = $totalThisMonth > 0 
            ? round(($completedThisMonth / $totalThisMonth) * 100) 
            : 0;

        // Last Month Efficiency
        $totalLastMonth = Appointment::where('clinic_id', $clinicId)
            ->whereMonth('appointment_date', Carbon::now()->subMonth()->month)
            ->count();
        $completedLastMonth = Appointment::where('clinic_id', $clinicId)
            ->whereMonth('appointment_date', Carbon::now()->subMonth()->month)
            ->where('status', 'completed')
            ->count();
        $this->lastMonthEfficiency = $totalLastMonth > 0 
            ? round(($completedLastMonth / $totalLastMonth) * 100, 1) 
            : 0;
    }

    public function render()
    {
        return view('livewire.clinicAdmin.dashboard')
            ->layout('components.layouts.app');
    }
}
