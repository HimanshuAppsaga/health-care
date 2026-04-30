<?php

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Extract API key from header (prefer header: X-API-KEY) or request parameter
        $apiKey = $request->header('X-API-KEY') ?: $request->input('api_key');

        // 2. Check if api_key is missing
        if (empty($apiKey)) {
            return response()->json([
                'status' => false,
                'message' => 'API key is required',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Query database for valid clinic
        // We use where('api_key', $apiKey) which is indexed for performance.
        $clinic = Clinic::where('api_key', $apiKey)->first();

        // 4. Check if api_key is invalid (not found)
        if (! $clinic) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid API key',
            ], Response::HTTP_FORBIDDEN);
        }

        // 5. Attach the clinic data to the request
        $request->merge(['clinic' => $clinic]);
        
        // Optional: Also set as a request attribute for easier access via $request->attributes->get('clinic')
        // but merge() is more standard for $request->clinic access in Laravel.
        $request->setUserResolver(fn () => $clinic); // Optional: treat clinic as the "user" for this request context

        return $next($request);
    }
}
