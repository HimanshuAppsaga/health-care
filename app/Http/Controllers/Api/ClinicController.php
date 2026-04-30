<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Display a listing of doctors for the authenticated clinic.
     */
    public function doctors(Request $request)
    {
        // Accessing the clinic attached by the CheckApiKey middleware
        $clinic = $request->clinic;

        // Fetching only clinic-specific data
        $doctors = Doctor::where('clinic_id', $clinic->id)->get();

        return response()->json([
            'status' => true,
            'clinic' => $clinic->name,
            'data' => $doctors,
        ]);
    }

    /**
     * Get clinic stats.
     */
    public function stats(Request $request)
    {
        $clinic = $request->clinic;

        return response()->json([
            'status' => true,
            'data' => [
                'doctors_count' => $clinic->doctors()->count(),
                'appointments_count' => $clinic->appointments()->count(),
            ],
        ]);
    }
}
