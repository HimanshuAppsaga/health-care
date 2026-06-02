<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class LogoutService
{
    /**
     * Handle the common logout logic for both Web and API.
     */
    public function logoutUser(): void
    {
        $user = request()->user();

        // Revoke Sanctum access token if it exists (Mobile API)
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        // Clear web session authentication (only for guards that support it, like SessionGuard)
        if (method_exists(Auth::guard(), 'logout')) {
            Auth::logout();
        }

        // Invalidate and regenerate session if it exists (Web App)
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
    }
}
