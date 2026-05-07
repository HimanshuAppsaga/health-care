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

                // Check availability (Same logic as API Controller)
                $date = Carbon::parse($dateStr);
                $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)
                $isToday = $date->isToday();

                $schedules = $doctor->getScheduleForDay($dayOfWeek);

                $hasAvailableSlot = false;

                foreach ($schedules as $schedule) {
                    $start = Carbon::createFromFormat('H:i:s', $schedule['start_time']);
                    $end = Carbon::createFromFormat('H:i:s', $schedule['end_time']);
                    $duration = $schedule['slot_duration'] ?: 30;

                    // If the schedule hasn't started yet today, don't allow bookings (Live Queue Logic)
                    if ($isToday && now()->isBefore($start)) {
                        continue;
                    }

                    // Check if there's at least one slot that is not in the past
                    $tempStart = $start->copy();
                    while ($tempStart->copy()->addMinutes($duration)->lte($end)) {
                        if ($isToday) {
                            if ($tempStart->copy()->addMinutes($duration)->isAfter(now())) {
                                $hasAvailableSlot = true;
                                break;
                            }
                        } else {
                            $hasAvailableSlot = true;
                            break;
                        }
                        $tempStart->addMinutes($duration);
                    }

                    if ($hasAvailableSlot) {
                        break;
                    }
                }

                if (! $hasAvailableSlot) {
                    return [
                        'success' => false,
                        'message' => 'No slots available for the selected date.',
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
}
