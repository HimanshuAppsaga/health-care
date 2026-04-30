<?php

namespace App\Http\Controllers\Api;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\Appointment as AppointmentModel;
use App\Models\Doctor;
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
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:20',
            'date' => 'nullable|date',
        ]);

        $doctorId = $request->doctor_id;
        $dateStr = $request->date ?? Carbon::today()->format('Y-m-d');
        $name = $request->name;
        $phone = $request->phone;

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
            'status' => 'pending',
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
