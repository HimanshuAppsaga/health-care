<?php

namespace Tests\Feature;

use App\Livewire\Auth\ForgotPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Password Recovery');
    }

    public function test_reset_link_can_be_requested(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        Livewire::test(ForgotPassword::class)
            ->set('email', 'test@example.com')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('status', trans(Password::RESET_LINK_SENT));
    }

    public function test_reset_link_request_requires_a_valid_email(): void
    {
        Livewire::test(ForgotPassword::class)
            ->set('email', 'invalid-email')
            ->call('sendResetLink')
            ->assertHasErrors(['email' => 'email']);
    }

    public function test_reset_link_request_requires_email(): void
    {
        Livewire::test(ForgotPassword::class)
            ->set('email', '')
            ->call('sendResetLink')
            ->assertHasErrors(['email' => 'required']);
    }

    public function test_reset_link_fails_if_email_does_not_exist(): void
    {
        Livewire::test(ForgotPassword::class)
            ->set('email', 'nonexistent@example.com')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }
}
