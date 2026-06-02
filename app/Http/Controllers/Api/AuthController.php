<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\LogoutRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Http\Resources\Api\LogoutResource;
use App\Http\Resources\Api\UserResource;
use App\Services\ApiService;
use App\Services\AuthenticationService;
use App\Services\LogoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AuthenticationService $authService
    ) {}

    /**
     * Handle mobile login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->login($request->only('email', 'password'));

            // Optionally revoke old tokens to keep only one active mobile session
            $user->tokens()->delete();

            $token = $user->createToken('mobile_api')->plainTextToken;

            return ApiService::respond('auth', [
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Login successful');
        } catch (ValidationException $e) {
            return ApiService::error($e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            return ApiService::error('Authentication failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Handle mobile registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->only('name', 'email', 'password'));

            $token = $user->createToken('mobile_api')->plainTextToken;

            return ApiService::respond('auth', [
                'user' => new UserResource($user),
                'token' => $token,
            ], 'Registration successful', 201);
        } catch (\Exception $e) {
            return ApiService::error('Registration failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Handle sending password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $message = $this->authService->forgotPassword($request->only('email'));

            return ApiService::message($message);
        } catch (ValidationException $e) {
            return ApiService::error($e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            return ApiService::error('Failed to send reset link: '.$e->getMessage(), 500);
        }
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $message = $this->authService->resetPassword(
                $request->only('email', 'otp', 'password', 'password_confirmation')
            );

            return ApiService::message($message);
        } catch (ValidationException $e) {
            return ApiService::error($e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            return ApiService::error('Password reset failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Handle mobile logout.
     */
    public function logout(LogoutRequest $request, LogoutService $logoutService): JsonResponse
    {
        try {
            $logoutService->logoutUser();

            return ApiService::respond('auth', new LogoutResource([]), 'Logout successful');
        } catch (\Exception $e) {
            return ApiService::error('Logout failed: '.$e->getMessage(), 500);
        }
    }
}
