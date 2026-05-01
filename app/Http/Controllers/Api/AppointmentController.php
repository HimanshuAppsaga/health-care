<?php

namespace App\Http\Controllers\Api;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\Appointment as AppointmentModel;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $clinic = $request->clinic;

        $request->validate([
            'doctor_id' => [
                'required',
                'exists:doctors,id',
                // Security: Ensure the doctor belongs to this clinic
                function ($attribute, $value, $fail) use ($clinic) {
                    if (! Doctor::where('id', $value)->where('clinic_id', $clinic->id)->exists()) {
                        $fail('The selected doctor does not belong to your clinic.');
                    }
                },
            ],
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'date' => 'nullable|date',
        ]);

        $doctorId = $request->doctor_id;
        $dateStr = $request->date ?? Carbon::today()->format('Y-m-d');
        $name = $request->name;
        $phone = $request->phone;

        // Check if the doctor has any schedule for the given day
        $date = Carbon::parse($dateStr);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $isToday = $date->isToday();

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        $hasAvailableSlot = false;

        foreach ($schedules as $schedule) {
            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $end = Carbon::createFromFormat('H:i:s', $schedule->end_time);
            $duration = $schedule->slot_duration ?: 30;

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
            return response()->json([
                'status' => false,
                'message' => 'No slots available for today.',
            ], 422);
        }

        // Find or create patient record by phone within THIS clinic
        $patient = Patient::where('phone', $phone)
            ->where('clinic_id', $clinic->id)
            ->first();

        if (! $patient) {
            $user = auth('sanctum')->user();
            $patient = Patient::create([
                'clinic_id' => $clinic->id,
                'user_id' => $user?->id,
                'name' => $name,
                'email' => $user?->email ?? 'guest@'.strtolower(str_replace(' ', '', $clinic->name)).'.com',
                'phone' => $phone,
                'dob' => '1990-01-01',
                'gender' => 'other',
            ]);
        } else {
            // Update patient details if changed
            $patient->update([
                'name' => $name,
                'user_id' => $patient->user_id ?? auth('sanctum')->id(),
            ]);
        }

        // Generate sequential token number for the clinic and day
        $tokenNumber = Queue::join('appointments', 'queues.appointment_id', '=', 'appointments.id')
            ->where('appointments.clinic_id', $clinic->id)
            ->whereDate('queues.created_at', Carbon::today())
            ->count() + 1;

        $appointment = AppointmentModel::create([
            'clinic_id' => $clinic->id,
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

        // Broadcast with clinic-specific context
        broadcast(new QueueUpdated($clinic->id, 'booked'))->toOthers();

        return response()->json([
            'status' => true,
            'message' => 'Appointment booked successfully!',
            'token' => $tokenNumber,
            'data' => [
                'appointment' => $appointment,
                'clinic' => $clinic->name,
            ],
        ], 201);
    }
}
