<?php

namespace App\Livewire\Common;

use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Appointments | ClinicOS')]
class AppointmentList extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $status = '';

    #[Url(history: true)]
    public $dateRange = 'all'; // all, today, week, month, custom

    #[Url(history: true)]
    public $startDate;

    #[Url(history: true)]
    public $endDate;

    public function mount()
    {
        if ($this->startDate || $this->endDate) {
            $this->dateRange = 'custom';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function updatedStartDate($value)
    {
        if ($value) {
            $this->dateRange = 'custom';
        }
        $this->resetPage();
    }

    public function updatedEndDate($value)
    {
        if ($value) {
            $this->dateRange = 'custom';
        }
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = Appointment::with(['doctor.user', 'patient.user', 'queue']);

        if ($user->hasRole('patient')) {
            $query->where('patient_id', $user->patient->id);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('phone', 'like', '%'.$this->search.'%')
                    ->orWhere('token', 'like', '%'.$this->search.'%');
            });
        }

        // Status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Date filter
        $this->applyDateFilter($query);

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderByRaw('CAST(token AS UNSIGNED) DESC')
            ->paginate(10);

        // Fetch schedules for the doctors and days of week in the current result set
        $doctorIds = $appointments->pluck('doctor_id')->unique();
        $daysOfWeek = $appointments->map(fn ($a) => Carbon::parse($a->appointment_date)->dayOfWeek)->unique();

        $schedules = DoctorSchedule::whereIn('doctor_id', $doctorIds)
            ->whereIn('day_of_week', $daysOfWeek)
            ->get();

        return view('livewire.common.appointment-list', [
            'appointments' => $appointments,
            'schedules' => $schedules,
        ]);
    }

    protected function applyDateFilter($query)
    {
        $today = Carbon::today();

        switch ($this->dateRange) {
            case 'today':
                $query->whereDate('appointment_date', $today);
                break;
            case 'week':
                $query->whereBetween('appointment_date', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('appointment_date', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()]);
                break;
            case 'custom':
                if ($this->startDate && $this->endDate) {
                    $query->whereBetween('appointment_date', [$this->startDate, $this->endDate]);
                } elseif ($this->startDate) {
                    $query->whereDate('appointment_date', $this->startDate);
                } elseif ($this->endDate) {
                    $query->whereDate('appointment_date', $this->endDate);
                }
                break;
        }
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;
        if ($range !== 'custom') {
            $this->startDate = null;
            $this->endDate = null;
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'status', 'dateRange', 'startDate', 'endDate']);
        $this->dateRange = 'all'; // Ensure it defaults back to all
        $this->resetPage();
    }
}
