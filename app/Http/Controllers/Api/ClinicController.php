<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClinicController extends Controller
{
    /**
     * Get details of the authenticated clinic.
     *
     * Uses caching for performance.
     */
    public function details(Request $request)
    {
        $clinic = $request->clinic;

        // Log clinic access
        Log::info('API Access: Clinic Details retrieved', ['clinic_id' => $clinic->id]);

        // Cache clinic details for 1 hour
        $cachedClinic = Cache::remember("clinic_details_{$clinic->id}", 3600, function () use ($clinic) {
            return new ClinicResource($clinic);
        });

        return response()->json([
            'status' => true,
            'data' => $cachedClinic,
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
