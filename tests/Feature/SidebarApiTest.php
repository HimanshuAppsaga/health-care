<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_gets_correct_menu(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'doctor']);
        $user->roles()->attach($role);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/sidebar');

        $response->assertStatus(200)
            ->assertJsonPath('role', 'doctor')
            ->assertJsonStructure(['role', 'menu' => ['Medical']]);
    }

    public function test_unauthenticated_user_cannot_access_sidebar(): void
    {
        $response = $this->getJson('/api/sidebar');

        $response->assertStatus(401);
    }
}
