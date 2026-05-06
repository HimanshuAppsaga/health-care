<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiService
{
    /**
     * Return a standardized success JSON response.
     *
     * @param  string  $key  The key for the data resource (e.g., 'patient')
     * @param  mixed  $data  The data to return
     * @param  string  $message  Success message
     * @param  int  $code  HTTP status code
     */
    public static function respond(string $key, mixed $data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => [
                $key => $data,
                'message' => $message,
            ],
        ], $code);
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param  string  $message  Error message
     * @param  int  $code  HTTP status code
     * @param  mixed  $errors  Optional validation errors or extra details
     */
    public static function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $responseData = [
            'message' => $message,
        ];

        if ($errors !== null) {
            $responseData['errors'] = $errors;
        }

        return response()->json([
            'data' => $responseData,
        ], $code);
    }

    /**
     * Return a simple message JSON response within the data wrapper.
     */
    public static function message(string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => [
                'message' => $message,
            ],
        ], $code);
    }
}
