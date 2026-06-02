<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use App\Services\ApiService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-api-key') ?? $request->input('api_key');

        if (! $apiKey) {
            return ApiService::error('API Key is missing', 401);
        }

        $clinic = Clinic::where('api_key', $apiKey)->first();

        if (! $clinic) {
            return ApiService::error('Invalid API Key', 401);
        }

        // Share the clinic context with the request
        $request->merge([
            'clinic' => $clinic,
            'clinic_id' => $clinic->id,
        ]);
        $request->attributes->add(['clinic' => $clinic]);

        return $next($request);
    }
}
