<?php

namespace App\Services;

use App\Events\QueueUpdated;
use App\Models\Appointment as AppointmentModel;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentBookingService
{
    /**
     * Book an appointment for a patient.
     *
     * @param  mixed  $user
     */
    public function bookAppointment(array $data, $user): array
    {
        try {
            return DB::transaction(function () use ($data, $user) {
                $clinicId = $data['clinic_id'] ?? null;
                $doctorId = $data['doctor_id'];
                $name = $data['name'];
                $phone = $data['phone'];
                $dateStr = $data['date'] ?? $data['appointment_date'] ?? Carbon::today()->format('Y-m-d');

                // Check if an appointment was already booked for this phone in the last 24 hours
                $alreadyBooked = AppointmentModel::where('phone', $phone)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->exists();

                if ($alreadyBooked) {
                    return [
                        'success' => false,
                        'message' => 'appointment is already booked.',
                    ];
                }

                // Get Doctor and Clinic context
                $doctor = Doctor::with('clinic')->find($doctorId);
                if (! $doctor) {
                    return [
                        'success' => false,
                        'message' => 'Doctor not found.',
                    ];
                }

                // If clinic_id is provided (API flow), ensure it matches the doctor
                if ($clinicId && $doctor->clinic_id != $clinicId) {
                    return [
                        'success' => false,
                        'message' => 'The selected doctor does not belong to your clinic.',
                    ];
                }

                $clinicId = $doctor->clinic_id;
                $clinic = $doctor->clinic;

                // Check availability using reusable logic
                $date = Carbon::parse($dateStr);
                $bookingStatus = $this->checkBookingStatus($doctor, $date);

                if (!$bookingStatus['allowed']) {
                    return [
                        'success' => false,
                        'message' => $bookingStatus['message'],
                    ];
                }

                // Find or create patient record by phone within THIS clinic
                $patient = Patient::where('phone', $phone)
                    ->where('clinic_id', $clinicId)
                    ->first();

                if (! $patient) {
                    $patient = Patient::create([
                        'clinic_id' => $clinicId,
                        'user_id' => $user?->id,
                        'name' => $name,
                        'email' => $user?->email ?? 'guest@'.strtolower(str_replace(' ', '', $clinic->name ?? 'clinic')).'.com',
                        'phone' => $phone,
                        'dob' => '1990-01-01',
                        'gender' => 'other',
                    ]);
                } else {
                    // Update patient details if changed
                    $patient->update([
                        'name' => $name,
                        'user_id' => $patient->user_id ?? $user?->id,
                    ]);
                }

                // Generate sequential token number for the clinic and day (Clinic-specific logic)
                $tokenNumber = Queue::join('appointments', 'queues.appointment_id', '=', 'appointments.id')
                    ->where('appointments.clinic_id', $clinicId)
                    ->whereDate('queues.created_at', Carbon::today())
                    ->count() + 1;

                $appointment = AppointmentModel::create([
                    'clinic_id' => $clinicId,
                    'doctor_id' => $doctorId,
                    'patient_id' => $patient->id,
                    'name' => $name,
                    'phone' => $phone,
                    'token' => $tokenNumber,
                    'appointment_date' => $dateStr,
                    'status' => 'pending',
                ]);

                Queue::create([
                    'appointment_id' => $appointment->id,
                    'token_number' => $tokenNumber,
                    'status' => 'waiting',
                ]);

                // Log success (from Livewire)
                Log::info('Appointment booked successfully', [
                    'appointment_id' => $appointment->id,
                    'token' => $tokenNumber,
                    'patient' => $name,
                ]);

                // Broadcast with clinic-specific context
                broadcast(new QueueUpdated($clinicId, 'booked'))->toOthers();

                return [
                    'success' => true,
                    'message' => 'Appointment booked successfully!',
                    'data' => [
                        'appointment' => $appointment,
                        'token' => $tokenNumber,
                        'clinic_name' => $clinic->name ?? 'Clinic',
                    ],
                ];
            });
        } catch (\Exception $e) {
            Log::error('Failed to book appointment: '.$e->getMessage(), [
                'name' => $data['name'] ?? 'unknown',
                'phone' => $data['phone'] ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Something went wrong while booking the appointment. Please try again.',
            ];
        }
    }

    /**
     * Check if appointment booking is allowed for a doctor on a specific date.
     *
     * @param  Doctor  $doctor
     * @param  Carbon  $date
     * @return array
     */
    public function checkBookingStatus(Doctor $doctor, Carbon $date): array
    {
        $dayOfWeek = $date->dayOfWeek;
        $schedules = $doctor->getScheduleForDay($dayOfWeek);

        if (empty($schedules)) {
            return [
                'allowed' => false,
                'message' => 'No slot available for today',
                'schedule' => null,
                'booking_start' => null,
                'booking_end' => null,
            ];
        }

        if (!$date->isToday()) {
            return [
                'allowed' => true,
                'message' => 'Allowed',
                'schedule' => $schedules[0] ?? null,
                'booking_start' => null,
                'booking_end' => null,
            ];
        }

        $now = now();

        // Sort schedules by start time
        usort($schedules, function ($a, $b) {
            return strcmp($a['start_time'], $b['start_time']);
        });

        $allPast = true;
        $upcomingSchedule = null;

        foreach ($schedules as $schedule) {
            $start = Carbon::createFromFormat('H:i:s', $schedule['start_time']);
            $end = Carbon::createFromFormat('H:i:s', $schedule['end_time']);
            
            // Set date to today
            $start->setDate($now->year, $now->month, $now->day);
            $end->setDate($now->year, $now->month, $now->day);

            $bookingStart = $start->copy()->subMinutes(15);
            $bookingEnd = $end->copy()->subMinutes(15);

            $bookingStartStr = $bookingStart->format('h:i A');
            $bookingEndStr = $bookingEnd->format('h:i A');

            if ($now->between($bookingStart, $bookingEnd)) {
                return [
                    'allowed' => true,
                    'message' => 'Allowed',
                    'schedule' => $schedule,
                    'booking_start' => $bookingStartStr,
                    'booking_end' => $bookingEndStr,
                ];
            }

            if ($now->isBefore($bookingStart)) {
                $allPast = false;
                if (!$upcomingSchedule) {
                    $upcomingSchedule = $schedule;
                }
            }
        }

        if ($upcomingSchedule) {
            $start = Carbon::createFromFormat('H:i:s', $upcomingSchedule['start_time']);
            $bookingStart = $start->copy()->subMinutes(15);
            return [
                'allowed' => false,
                'message' => 'Appointment booking will start at ' . $bookingStart->format('h:i A'),
                'schedule' => $upcomingSchedule,
                'booking_start' => $bookingStart->format('h:i A'),
                'booking_end' => Carbon::createFromFormat('H:i:s', $upcomingSchedule['end_time'])->subMinutes(15)->format('h:i A'),
            ];
        }

        if ($allPast) {
            $lastSchedule = end($schedules);
            $start = Carbon::createFromFormat('H:i:s', $lastSchedule['start_time']);
            $end = Carbon::createFromFormat('H:i:s', $lastSchedule['end_time']);
            return [
                'allowed' => false,
                'message' => "Today's appointment booking time has ended",
                'schedule' => $lastSchedule,
                'booking_start' => $start->subMinutes(15)->format('h:i A'),
                'booking_end' => $end->subMinutes(15)->format('h:i A'),
            ];
        }

        return [
            'allowed' => false,
            'message' => 'No slot available for today',
            'schedule' => null,
            'booking_start' => null,
            'booking_end' => null,
        ];
    }
}
