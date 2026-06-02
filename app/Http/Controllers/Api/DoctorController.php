<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Services\ApiService;

class DoctorController extends Controller
{
    /**
     * Display a listing of doctors for the authenticated clinic.
     *
     * Supports pagination.
     */
    public function index(IndexDoctorRequest $request)
    {
        $doctors = $request->getDoctors();

        return ApiService::respond(
            'doctors',
            DoctorResource::collection($doctors),
            'Doctor list retrieved successfully'
        );
    }
}
