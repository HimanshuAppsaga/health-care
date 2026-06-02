<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\LogoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LogoutService $logoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logoutService = new LogoutService();
    }

    public function test_logout_user_clears_session_and_web_auth()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Start a session
        session()->put('foo', 'bar');
        
        // Assert session has data
        $this->assertTrue(session()->has('foo'));
        $this->assertAuthenticatedAs($user);

        $this->logoutService->logoutUser();

        // After logout, session should be invalidated and user logged out
        $this->assertFalse(session()->has('foo'));
        $this->assertGuest();
    }
}
