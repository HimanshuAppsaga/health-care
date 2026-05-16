<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        protected AppointmentService $appointmentService
    ) {}

    /**
     * Display today's appointments for the web view.
     */
    public function index(Request $request)
    {
        $appointments = $this->appointmentService->getTodayAppointments([
            'doctor_id' => $request->doctor_id,
            'clinic_id' => auth()->user()->clinic_id,
        ]);

        return view('appointments.today', compact('appointments'));
    }
}
