<?php

namespace App\Livewire\Common;

use App\Services\AppointmentService;
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
        if (auth()->user()->hasRole('patient')) {
            return $this->redirect(route('patient.dashboard'), navigate: true);
        }

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
        $appointmentService = app(AppointmentService::class);

        $filters = [
            'search' => $this->search,
            'status' => $this->status,
            'date_range' => $this->dateRange,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'clinic_id' => $user->clinic_id,
        ];

        if ($user->hasRole('patient')) {
            $filters['patient_id'] = $user->patient?->id;
        }

        $appointments = $appointmentService->getAppointments($filters, true);

        return view('livewire.common.appointment-list', [
            'appointments' => $appointments,
        ]);
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
