<?php

namespace Tests\Feature\Api;

use App\Enums\AppointmentStatus;
use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueCallNextRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    protected $clinic;

    protected $doctor;

    protected $patient1;

    protected $patient2;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Dr. Test',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => bcrypt('password'),
        ]);

        $this->clinic = Clinic::create([
            'name' => 'Test Clinic',
            'api_key' => 'test-api-key',
        ]);

        $this->doctor = Doctor::create([
            'clinic_id' => $this->clinic->id,
            'user_id' => $user->id,
            'specialization' => 'General',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'consultation_fee' => 500,
            'is_on_hold' => false,
        ]);

        $this->patient1 = Patient::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'email' => 'jane@example.com',
            'dob' => '1990-01-01',
            'gender' => 'female',
        ]);

        $this->patient2 = Patient::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'John Doe',
            'phone' => '1122334455',
            'email' => 'john@example.com',
            'dob' => '1995-01-01',
            'gender' => 'male',
        ]);
    }

    public function test_it_allows_calling_next_when_no_patient_is_being_served()
    {
        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient1->id,
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'appointment_date' => now()->format('Y-m-d'),
            'status' => AppointmentStatus::PENDING,
            'token' => '1',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'token_number' => '1',
            'status' => QueueStatus::WAITING,
        ]);

        $response = $this->postJson('/api/queue/call-next', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Next patient called successfully');
    }

    public function test_it_fails_calling_next_when_doctor_is_on_hold()
    {
        $this->doctor->update(['is_on_hold' => true]);

        $response = $this->postJson('/api/queue/call-next', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['doctor_id']);
        $this->assertStringContainsString('The doctor is currently on hold', $response->json('errors.doctor_id.0'));
    }

    public function test_it_fails_calling_next_when_a_patient_is_already_being_served()
    {
        // 1. Create a patient who is already "serving"
        $appointment1 = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient1->id,
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'appointment_date' => now()->format('Y-m-d'),
            'status' => AppointmentStatus::PENDING, // Appointment stays pending while serving in queue
            'token' => '1',
        ]);

        Queue::create([
            'appointment_id' => $appointment1->id,
            'token_number' => '1',
            'status' => QueueStatus::SERVING,
        ]);

        // 2. Create another patient in "waiting"
        $appointment2 = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient2->id,
            'name' => 'John Doe',
            'phone' => '1122334455',
            'appointment_date' => now()->format('Y-m-d'),
            'status' => AppointmentStatus::PENDING,
            'token' => '2',
        ]);

        Queue::create([
            'appointment_id' => $appointment2->id,
            'token_number' => '2',
            'status' => QueueStatus::WAITING,
        ]);

        // 3. Attempt to call-next should fail
        $response = $this->postJson('/api/queue/call-next', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['doctor_id']);
        $this->assertStringContainsString('A patient is already being served', $response->json('errors.doctor_id.0'));
    }
}
