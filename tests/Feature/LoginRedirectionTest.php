<?php

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginRedirectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'receptionist']);
        Role::create(['name' => 'patient']);
    }

    public function test_receptionist_is_redirected_to_receptionist_dashboard(): void
    {
        $user = User::factory()->create();
        $user->update(['role_id' => Role::where('name', 'receptionist')->first()->id]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('receptionist.dashboard'));
    }

    public function test_patient_is_redirected_to_patient_dashboard(): void
    {
        $user = User::factory()->create();
        $user->update(['role_id' => Role::where('name', 'patient')->first()->id]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('appointments.index'));
    }

    public function test_authenticated_receptionist_visiting_login_is_redirected(): void
    {
        $user = User::factory()->create();
        $user->update(['role_id' => Role::where('name', 'receptionist')->first()->id]);

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('receptionist.dashboard'));
    }
}
