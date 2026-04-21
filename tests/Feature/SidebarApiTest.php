<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_admin_gets_correct_menu(): void
    {
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $role = Role::firstOrCreate(['name' => 'clinic_admin']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/sidebar');

        $response->assertStatus(200)
            ->assertJsonPath('role', 'clinic_admin')
            ->assertJsonStructure(['role', 'menu' => ['Management', 'Medical', 'System']]);
    }

    public function test_doctor_gets_correct_menu(): void
    {
        $clinic = Clinic::factory()->create();
        $user = User::factory()->create(['clinic_id' => $clinic->id]);
        $role = Role::firstOrCreate(['name' => 'doctor']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/sidebar');

        $response->assertStatus(200)
            ->assertJsonPath('role', 'doctor')
            ->assertJsonStructure(['role', 'menu' => ['Medical', 'System']]);
    }

    public function test_unauthenticated_user_cannot_access_sidebar(): void
    {
        $response = $this->getJson('/api/sidebar');

        $response->assertStatus(401);
    }
}
