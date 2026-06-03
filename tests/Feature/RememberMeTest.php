<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cookie;
use Livewire\Livewire;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_remember_me()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $component = Livewire::test(Login::class)
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('remember', true)
            ->call('authenticate');

        $this->assertAuthenticatedAs($user);

        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_remember_me_cookie_logs_user_in_after_session_ends()
    {
        $user = User::factory()->create([
            'email' => 'test2@example.com',
            'password' => bcrypt('password'),
        ]);

        $component = Livewire::test(Login::class)
            ->set('email', 'test2@example.com')
            ->set('password', 'password')
            ->set('remember', true)
            ->call('authenticate');

        $this->assertAuthenticatedAs($user);

        $cookies = response('')->headers->getCookies();
        // Wait, queued cookies are in Cookie::getQueuedCookies()
        $queuedCookies = Cookie::getQueuedCookies();

        $rememberCookie = null;
        foreach ($queuedCookies as $cookie) {
            if (str_starts_with($cookie->getName(), 'remember_web_')) {
                $rememberCookie = $cookie;
                break;
            }
        }

        $this->assertNotNull($rememberCookie, 'Remember cookie was not queued by Livewire!');
    }

    public function test_user_login_without_remember_me_does_not_queue_remember_cookie()
    {
        $user = User::factory()->create([
            'email' => 'test3@example.com',
            'password' => bcrypt('password'),
        ]);

        $component = Livewire::test(Login::class)
            ->set('email', 'test3@example.com')
            ->set('password', 'password')
            ->set('remember', false)
            ->call('authenticate');

        $this->assertAuthenticatedAs($user);

        $queuedCookies = Cookie::getQueuedCookies();

        $rememberCookie = null;
        foreach ($queuedCookies as $cookie) {
            if (str_starts_with($cookie->getName(), 'remember_web_')) {
                $rememberCookie = $cookie;
                break;
            }
        }

        $this->assertNull($rememberCookie, 'Remember cookie was queued when remember me was false!');
    }
}
