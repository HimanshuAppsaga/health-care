<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Role $doctorRole;

    protected Role $receptionistRole;

    protected Role $patientRole;

    protected User $doctorUser;

    protected User $receptionistUser;

    protected User $patientUser;

    protected Clinic $clinic;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $this->receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);
        $this->patientRole = Role::firstOrCreate(['name' => 'patient']);

        // Create a default clinic
        $this->clinic = Clinic::firstOrCreate(
            ['id' => 1],
            ['name' => 'Default Clinic', 'address' => 'Main Street']
        );

        // Create standard doctor user and ensure doctor profile exists
        $this->doctorUser = User::factory()->create(['role_id' => $this->doctorRole->id]);
        $this->doctorUser->ensureDoctorProfileExists();

        // Create receptionist user
        $this->receptionistUser = User::factory()->create(['role_id' => $this->receptionistRole->id]);

        // Create patient user
        $this->patientUser = User::factory()->create(['role_id' => $this->patientRole->id]);
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $protectedRoutes = [
            route('receptionist.dashboard'),
            route('receptionist.book-appointment'),
            route('doctor.dashboard'),
            route('doctor.assign-role'),
            route('doctor.clinic-settings'),
            route('doctor.clinic.detail', ['id' => $this->clinic->id]),
            route('doctor.clinic.edit', ['id' => $this->clinic->id]),
            route('doctor.profile.detail', ['id' => $this->doctorUser->doctor->id]),
            route('doctor.profile.edit', ['id' => $this->doctorUser->doctor->id]),
            route('patient.dashboard'),
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_patient_cannot_access_doctor_or_receptionist_routes(): void
    {
        $unauthorizedRoutes = [
            route('receptionist.dashboard'),
            route('receptionist.book-appointment'),
            route('doctor.dashboard'),
            route('doctor.assign-role'),
            route('doctor.clinic-settings'),
            route('doctor.clinic.detail', ['id' => $this->clinic->id]),
            route('doctor.clinic.edit', ['id' => $this->clinic->id]),
            route('doctor.profile.detail', ['id' => $this->doctorUser->doctor->id]),
            route('doctor.profile.edit', ['id' => $this->doctorUser->doctor->id]),
        ];

        foreach ($unauthorizedRoutes as $route) {
            $response = $this->actingAs($this->patientUser)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_doctor_cannot_access_receptionist_or_patient_routes(): void
    {
        $unauthorizedRoutes = [
            route('receptionist.dashboard'),
            route('receptionist.book-appointment'),
            route('patient.dashboard'),
        ];

        foreach ($unauthorizedRoutes as $route) {
            $response = $this->actingAs($this->doctorUser)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_receptionist_cannot_access_doctor_or_patient_routes(): void
    {
        $unauthorizedRoutes = [
            route('doctor.dashboard'),
            route('doctor.assign-role'),
            route('doctor.clinic-settings'),
            route('doctor.clinic.detail', ['id' => $this->clinic->id]),
            route('doctor.clinic.edit', ['id' => $this->clinic->id]),
            route('doctor.profile.detail', ['id' => $this->doctorUser->doctor->id]),
            route('doctor.profile.edit', ['id' => $this->doctorUser->doctor->id]),
            route('patient.dashboard'),
        ];

        foreach ($unauthorizedRoutes as $route) {
            $response = $this->actingAs($this->receptionistUser)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_authorized_users_can_access_their_own_routes(): void
    {
        // Patient dashboard
        $response = $this->actingAs($this->patientUser)->get(route('patient.dashboard'));
        $response->assertStatus(200);

        // Receptionist dashboard
        $response = $this->actingAs($this->receptionistUser)->get(route('receptionist.dashboard'));
        $response->assertStatus(200);

        // Doctor dashboard
        $response = $this->actingAs($this->doctorUser)->get(route('doctor.dashboard'));
        $response->assertStatus(200);
    }
}
