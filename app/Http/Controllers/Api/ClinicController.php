<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClinicDetailsRequest;
use App\Http\Resources\Api\ClinicResource as ClinicDetailsResource;
use App\Http\Resources\Api\ClinicStatsResource;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Get details of the authenticated clinic.
     *
     * Uses caching for performance.
     */
    public function details(ClinicDetailsRequest $request)
    {
        return new ClinicDetailsResource($request->getCachedClinicDetails());
    }

    /**
     * Get clinic stats.
     */
    public function stats(Request $request)
    {
        return new ClinicStatsResource($request->clinic);
    }
}
