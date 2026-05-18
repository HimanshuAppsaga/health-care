<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
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
     * Reset the user's password using the given token and password.
     *
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $data
     *
     * @throws ValidationException
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                ]);

                $user->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        }

        return trans($status);
    }
}
