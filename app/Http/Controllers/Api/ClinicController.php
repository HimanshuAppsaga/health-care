<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClinicDetailsRequest;
use App\Http\Resources\Api\ClinicResource as ClinicDetailsResource;
use App\Http\Resources\Api\ClinicStatsResource;
use App\Services\ApiService;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Get details of the authenticated clinic.
     */
    public function details(ClinicDetailsRequest $request)
    {
        return ApiService::respond(
            'clinic',
            new ClinicDetailsResource($request->getClinicDetails()),
            'Clinic details retrieved successfully'
        );
    }

    /**
     * Get clinic stats.
     */
    public function stats(Request $request)
    {
        return new ClinicStatsResource($request->clinic);
    }
}
