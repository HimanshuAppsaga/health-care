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
     * Get available slots for a specific doctor and date.
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'nullable|date',
        ]);

        $doctorId = $request->doctor_id;
        $dateStr = $request->date ?? Carbon::today()->format('Y-m-d');

        $availableSlots = $this->getAvailableSlotsArray($doctorId, $dateStr);

        return response()->json([
            'date' => $dateStr,
            'doctor_id' => $doctorId,
            'available_slots' => $availableSlots,
        ]);
    }

    protected function getAvailableSlotsArray($doctorId, $dateStr)
    {
        $date = Carbon::parse($dateStr);
        $dayOfWeek = $date->dayOfWeek;

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $bookedSlots = AppointmentModel::where('doctor_id', $doctorId)
            ->where('appointment_date', $dateStr)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        $availableSlots = [];

        foreach ($schedules as $schedule) {
            $start = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $end = Carbon::createFromFormat('H:i:s', $schedule->end_time);
            $duration = $schedule->slot_duration ?: 30;

            if ($dateStr === Carbon::today()->format('Y-m-d') && now()->isBefore($start)) {
                continue;
            }

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $timeSlot = $start->format('H:i');

                $isPast = false;
                if ($dateStr === Carbon::today()->format('Y-m-d')) {
                    if ($start->copy()->addMinutes($duration)->isBefore(now())) {
                        $isPast = true;
                    }
                }

                if (! $isPast && ! in_array($timeSlot, $bookedSlots)) {
                    $availableSlots[] = $timeSlot;
                }
                $start->addMinutes($duration);
            }
        }

        sort($availableSlots);

        return array_values(array_unique($availableSlots));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'selectedSlot' => 'nullable|date_format:H:i',
            'selected_slot' => 'nullable|date_format:H:i',
            'date' => 'nullable|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $doctorId = $request->doctor_id;
        $dateStr = $request->date ?? Carbon::today()->format('Y-m-d');
        $slot = $request->selectedSlot ?? $request->selected_slot;

        // Auto-assign the first available slot if not provided
        if (! $slot) {
            $availableSlots = $this->getAvailableSlotsArray($doctorId, $dateStr);
            if (empty($availableSlots)) {
                return response()->json(['message' => 'No available slots for this date.'], 422);
            }
            $slot = $availableSlots[0];
        }

        $name = $request->name;
        $phone = $request->phone;

        // Fetch already booked slots
        $bookedSlots = AppointmentModel::where('doctor_id', $doctorId)
            ->where('appointment_date', $dateStr)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->toArray();

        // Double check slot availability
        if (in_array($slot, $bookedSlots)) {
            return response()->json(['message' => 'Selected slot is no longer available. Please pick another one.'], 422);
        }

        // Fetch doctor to get the clinic_id
        $doctor = Doctor::findOrFail($doctorId);
        $clinicId = $doctor->clinic_id ?? 1;

        // Find or create patient record by phone
        $patient = Patient::where('phone', $phone)->first();

        if (! $patient) {
            $user = auth('sanctum')->user();
            $patient = Patient::create([
                'clinic_id' => $clinicId,
                'user_id' => $user?->id,
                'name' => $name,
                'email' => $user?->email ?? 'guest@example.com',
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

        // Generate simple sequential token number for the day
        $tokenNumber = Queue::whereDate('created_at', Carbon::today())->count() + 1;

        $appointment = AppointmentModel::create([
            'clinic_id' => $clinicId,
            'doctor_id' => $doctorId,
            'patient_id' => $patient->id,
            'name' => $name,
            'phone' => $phone,
            'token' => $tokenNumber,
            'appointment_date' => $dateStr,
            'start_time' => $slot,
            'end_time' => Carbon::parse($slot)->addMinutes(30)->format('H:i'), // Default 30 min
            'status' => 'pending',
            'notes' => ($request->reason ?? 'Checkup').($request->notes ? "\n".$request->notes : ''),
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'token_number' => $tokenNumber,
            'status' => 'waiting',
        ]);

        broadcast(new QueueUpdated(1, 'booked'))->toOthers();

        return response()->json([
            'message' => 'Appointment booked successfully!',
            'token' => $tokenNumber,
            'appointment' => $appointment,
        ], 201);
    }
}
