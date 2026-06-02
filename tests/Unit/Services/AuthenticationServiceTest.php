<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthenticationService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthenticationService();
    }

    public function test_login_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $result = $this->authService->login([
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_failure()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $this->expectException(ValidationException::class);

        $this->authService->login([
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);
    }

    public function test_register_success()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'phone' => '1234567890',
        ];

        $user = $this->authService->register($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('johndoe@example.com', $user->email);
        $this->assertEquals('1234567890', $user->phone);
        
        $role = Role::where('name', 'patient')->first();
        $this->assertEquals($role->id, $user->role_id);
    }

    public function test_forgot_password_success()
    {
        $user = User::factory()->create();

        // Should return a string message on success
        $result = $this->authService->forgotPassword(['email' => $user->email]);
        
        $this->assertEquals(trans(Password::RESET_LINK_SENT), $result);
    }

    public function test_forgot_password_failure()
    {
        $this->expectException(ValidationException::class);

        $this->authService->forgotPassword(['email' => 'nonexistent@example.com']);
    }

    public function test_reset_password_success()
    {
        $user = User::factory()->create();
        $otp = '1234';
        
        Cache::put("password_reset_otp_{$user->email}", $otp, now()->addMinutes(10));

        $data = [
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'otp' => $otp,
        ];

        $result = $this->authService->resetPassword($data);

        $this->assertEquals(trans('passwords.reset'), $result);
        $this->assertNull(Cache::get("password_reset_otp_{$user->email}"));
        
        // Assert password changed
        $this->assertTrue(Auth::attempt(['email' => $user->email, 'password' => 'newpassword123']));
    }

    public function test_reset_password_invalid_otp()
    {
        $user = User::factory()->create();
        
        Cache::put("password_reset_otp_{$user->email}", '1234', now()->addMinutes(10));

        $data = [
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'otp' => '9999',
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The provided 4-digit code is invalid or has expired.');

        $this->authService->resetPassword($data);
    }
}
