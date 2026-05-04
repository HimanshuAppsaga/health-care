<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Services\AppointmentBookingService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentBookingService $bookingService
    ) {}

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

        $data = $request->all();
        $data['clinic_id'] = $clinic->id;

        $result = $this->bookingService->bookAppointment($data, auth('sanctum')->user());

        if (! $result['success']) {
            return response()->json([
                'status' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => $result['message'],
            'token' => $result['data']['token'],
            'data' => [
                'appointment' => $result['data']['appointment'],
                'clinic' => $result['data']['clinic_name'],
            ],
        ], 201);
    }
}
