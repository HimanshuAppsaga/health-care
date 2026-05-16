<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AppointmentService
{
    /**
     * Get appointments with filters.
     */
    public function getAppointments(array $filters = [], bool $paginate = false): mixed
    {
        $dateRange = $filters['date_range'] ?? 'today';
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $doctorId = $filters['doctor_id'] ?? null;
        $clinicId = $filters['clinic_id'] ?? null;
        $patientId = $filters['patient_id'] ?? null;
        $status = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;

        $query = Appointment::query()
            ->with(['doctor.user', 'patient', 'queue', 'clinic'])
            ->when($clinicId, fn ($q) => $q->where('clinic_id', $clinicId))
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->when($patientId, fn ($q) => $q->where('patient_id', $patientId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('token', 'like', '%'.$search.'%');
                });
            });

        $this->applyDateFilter($query, $dateRange, $startDate, $endDate);

        $query->orderBy('appointment_date', 'desc')
            ->orderByRaw('CAST(token AS UNSIGNED) DESC');

        return $paginate ? $query->paginate(10) : $query->get();
    }

    /**
     * Apply date filters to the query.
     */
    protected function applyDateFilter($query, $range, $startDate, $endDate)
    {
        $today = Carbon::today();

        switch ($range) {
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
                if ($startDate && $endDate) {
                    $query->whereBetween('appointment_date', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->whereDate('appointment_date', $startDate);
                } elseif ($endDate) {
                    $query->whereDate('appointment_date', $endDate);
                }
                break;
        }
    }

    /**
     * Get today's appointments specifically.
     */
    public function getTodayAppointments(array $filters = []): Collection
    {
        $filters['date_range'] = 'today';

        return $this->getAppointments($filters);
    }
}
