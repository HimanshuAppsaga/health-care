<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleService
{
    /**
     * Get today's schedules for a specific doctor or all doctors in a clinic.
     */
    public function getTodaySchedules(?int $doctorId = null, ?int $clinicId = null): Collection
    {
        $today = Carbon::today();
        $dayOfWeek = $today->dayOfWeek;

        $query = Doctor::with(['user', 'clinic']);

        if ($doctorId) {
            $query->where('id', $doctorId);
        }

        if ($clinicId) {
            $query->where('clinic_id', $clinicId);
        }

        $doctors = $query->get();
        $allSchedules = collect();

        foreach ($doctors as $doctor) {
            $sessions = $doctor->getScheduleForDay($dayOfWeek);

            foreach ($sessions as $session) {
                $startTime = Carbon::createFromFormat('H:i:s', $session['start_time'])->setDate($today->year, $today->month, $today->day);
                $endTime = Carbon::createFromFormat('H:i:s', $session['end_time'])->setDate($today->year, $today->month, $today->day);

                $status = $this->determineStatus($startTime, $endTime);

                $allSchedules->push((object) [
                    'id' => $doctor->id.'-'.$startTime->format('Hi'),
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->user->name ?? 'Unknown',
                    'start_time' => $startTime->format('h:i A'),
                    'end_time' => $endTime->format('h:i A'),
                    'status' => $status,
                    'booked_count' => $this->getBookedCount($doctor->id, $today),
                    'doctor' => $doctor, // For resource mapping
                ]);
            }
        }

        return $allSchedules->sortBy('start_time');
    }

    /**
     * Determine if a schedule is active, pending, or ended based on current time.
     */
    private function determineStatus(Carbon $start, Carbon $end): string
    {
        $now = now();

        if ($now->between($start, $end)) {
            return 'active';
        }

        if ($now->isBefore($start)) {
            return 'pending';
        }

        return 'ended';
    }

    /**
     * Get the count of appointments for a doctor today.
     */
    private function getBookedCount(int $doctorId, Carbon $date): int
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->count();
    }
}
