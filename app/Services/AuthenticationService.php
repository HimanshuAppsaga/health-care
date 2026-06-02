<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Authenticate a user by credentials.
     *
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember = false): User
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        return Auth::user();
    }

    /**
     * Register a new user with patient role.
     *
     * @param  array{name: string, email: string, password: string, phone?: string|null}  $data
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'patient']);
        if ($user->role_id !== $role->id) {
            $user->role_id = $role->id;
            $user->save();
        }

        return $user;
    }

    /**
     * Send a password reset link to the given user.
     *
     * @param  array{email: string}  $data
     *
     * @throws ValidationException
     */
    public function forgotPassword(array $data): string
    {
        $status = Password::sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return trans($status);
    }

    /**
     * Reset the user's password using the given email and OTP.
     *
     * @param  array{email: string, password: string, password_confirmation: string, otp: string, token?: string}  $data
     *
     * @throws ValidationException
     */
    public function resetPassword(array $data): string
    {
        $cachedOtp = Cache::get("password_reset_otp_{$data['email']}");
        if (! $cachedOtp || $cachedOtp !== $data['otp']) {
            throw ValidationException::withMessages([
                'otp' => ['The provided 4-digit code is invalid or has expired.'],
            ]);
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [trans('passwords.user')],
            ]);
        }

        if (isset($data['token']) && ! Password::broker()->tokenExists($user, $data['token'])) {
            throw ValidationException::withMessages([
                'email' => [trans('passwords.token')],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ]);

        $user->setRememberToken(Str::random(60));
        $user->save();

        event(new PasswordReset($user));

        Cache::forget("password_reset_otp_{$data['email']}");

        // Also clean up any lingering database tokens if they exist
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return trans('passwords.reset');
    }
}
