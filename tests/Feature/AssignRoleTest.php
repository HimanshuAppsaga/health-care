<?php

namespace Tests\Feature;

use App\Livewire\Doctor\AssignRole;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssignRoleTest extends TestCase
{
    use RefreshDatabase;

    protected User $doctorUser;

    protected User $patientUser;

    protected Role $doctorRole;

    protected Role $receptionistRole;

    protected Role $patientRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles
        $this->doctorRole = Role::firstOrCreate(['name' => 'doctor']);
        $this->receptionistRole = Role::firstOrCreate(['name' => 'receptionist']);
        $this->patientRole = Role::firstOrCreate(['name' => 'patient']);

        // Create standard doctor user
        $this->doctorUser = User::factory()->create(['role_id' => $this->doctorRole->id]);

        // Create patient user
        $this->patientUser = User::factory()->create(['role_id' => $this->patientRole->id]);
    }

    public function test_guest_cannot_access_assign_role_page(): void
    {
        $response = $this->get(route('doctor.assign-role'));
        $response->assertRedirect(route('login'));
    }

    public function test_patient_cannot_access_assign_role_page(): void
    {
        $response = $this->actingAs($this->patientUser)
            ->get(route('doctor.assign-role'));

        $response->assertStatus(403);
    }

    public function test_doctor_can_access_assign_role_page(): void
    {
        $response = $this->actingAs($this->doctorUser)
            ->get(route('doctor.assign-role'));

        $response->assertStatus(200)
            ->assertSeeLivewire(AssignRole::class);
    }

    public function test_validation_errors_when_assigning_role(): void
    {
        Livewire::actingAs($this->doctorUser)
            ->test(AssignRole::class)
            ->set('email', '')
            ->set('role_id', '')
            ->call('assign')
            ->assertHasErrors(['email' => 'required', 'role_id' => 'required']);

        Livewire::actingAs($this->doctorUser)
            ->test(AssignRole::class)
            ->set('email', 'not-an-email')
            ->set('role_id', '999')
            ->call('assign')
            ->assertHasErrors(['email' => 'email', 'role_id' => 'in']);

        Livewire::actingAs($this->doctorUser)
            ->test(AssignRole::class)
            ->set('email', 'doesnotexist@example.com')
            ->set('role_id', '1')
            ->call('assign')
            ->assertHasErrors(['email' => 'exists']);
    }

    public function test_successful_role_assignment_to_receptionist(): void
    {
        // Assert initial role is patient
        $this->assertEquals($this->patientRole->id, $this->patientUser->role_id);

        Livewire::actingAs($this->doctorUser)
            ->test(AssignRole::class)
            ->set('email', $this->patientUser->email)
            ->set('role_id', (string) $this->receptionistRole->id)
            ->call('assign')
            ->assertHasNoErrors()
            ->assertStatus(200);

        // Refresh and assert updated role
        $this->patientUser->refresh();
        $this->assertEquals($this->receptionistRole->id, $this->patientUser->role_id);
    }

    public function test_successful_role_assignment_to_doctor_creates_profile(): void
    {
        // Setup a default clinic since Doctor creation needs it
        Clinic::firstOrCreate(
            ['id' => 1],
            ['name' => 'Default Clinic', 'address' => 'Main Street']
        );

        $targetUser = User::factory()->create(['role_id' => $this->patientRole->id]);

        // Initially no doctor profile
        $this->assertNull($targetUser->doctor);

        Livewire::actingAs($this->doctorUser)
            ->test(AssignRole::class)
            ->set('email', $targetUser->email)
            ->set('role_id', (string) $this->doctorRole->id)
            ->call('assign')
            ->assertHasNoErrors()
            ->assertStatus(200);

        // Refresh and assert role & doctor profile existence
        $targetUser->refresh();
        $this->assertEquals($this->doctorRole->id, $targetUser->role_id);
        $this->assertNotNull($targetUser->doctor);
        $this->assertEquals(1, $targetUser->doctor->clinic_id);
    }

    public function test_doctor_profile_excluded_from_active_doctors_when_role_changed_to_receptionist(): void
    {
        Clinic::firstOrCreate(
            ['id' => 1],
            ['name' => 'Default Clinic', 'address' => 'Main Street']
        );

        // Initially create a doctor user (which ensures profile exists)
        $user = User::factory()->create(['role_id' => $this->doctorRole->id]);
        $user->ensureDoctorProfileExists();

        // Verify they are initially fetched in active doctors
        $this->assertTrue(Doctor::activeDoctor()->where('user_id', $user->id)->exists());

        // Update their role to receptionist
        Livewire::actingAs($this->doctorUser)
            ->test(AssignRole::class)
            ->set('email', $user->email)
            ->set('role_id', (string) $this->receptionistRole->id)
            ->call('assign')
            ->assertHasNoErrors();

        // Verify their user has receptionist role and doctor model is deleted from the database
        $user->refresh();
        $this->assertEquals($this->receptionistRole->id, $user->role_id);
        $this->assertNull($user->doctor);

        // Verify they are now EXCLUDED from active doctors
        $this->assertFalse(Doctor::activeDoctor()->where('user_id', $user->id)->exists());
    }
}
