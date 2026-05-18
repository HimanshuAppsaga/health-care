<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use App\Services\ApiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY') ?: $request->input('api_key');

        if (empty($apiKey)) {
            return ApiService::error('API key is required', Response::HTTP_UNAUTHORIZED);
        }

        $clinic = Clinic::where('api_key', $apiKey)->first();

        if (! $clinic) {
            return ApiService::error('Invalid API key', Response::HTTP_FORBIDDEN);
        }

        // Attach clinic context to the request for downstream controllers/requests
        $request->merge([
            'clinic' => $clinic,
            'clinic_id' => $clinic->id,
        ]);

        $request->attributes->add(['clinic' => $clinic]);

        return $next($request);
    }
}
