<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors for the authenticated clinic.
     *
     * Supports pagination.
     */
    public function index(Request $request)
    {
        $clinic = $request->clinic;

        // Log clinic access
        Log::info('API Access: Doctor list retrieved', ['clinic_id' => $clinic->id]);

        // Fetch doctors with user relationship to avoid N+1
        $doctors = Doctor::with('user')
            ->where('clinic_id', $clinic->id)
            ->paginate(10);

        return ApiService::respond(
            'doctors',
            DoctorResource::collection($doctors),
            'Doctor list retrieved successfully'
        );
    }
}
