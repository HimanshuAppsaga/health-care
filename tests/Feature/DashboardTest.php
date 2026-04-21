<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_successfully()
    {
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Clinic Overview');
    }

    public function test_dashboard_component_exists()
    {
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
        ]);

        $this->actingAs($user);
        
        Livewire::test(\App\Livewire\ClinicAdmin\Dashboard::class)
            ->assertStatus(200)
            ->assertViewHas('stats');
    }
}
