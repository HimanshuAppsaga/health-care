<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueCallNextOnHoldTest extends TestCase
{
    use RefreshDatabase;

    protected $clinic;
    protected $doctor;
    protected $patient;

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

        $this->patient = Patient::create([
            'clinic_id' => $this->clinic->id,
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'email' => 'jane@example.com',
            'dob' => '1990-01-01',
            'gender' => 'female',
        ]);

        $appointment = Appointment::create([
            'clinic_id' => $this->clinic->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'name' => 'Jane Doe',
            'phone' => '9876543210',
            'appointment_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'token' => '1',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'token_number' => '1',
            'status' => 'waiting',
        ]);
    }

    public function test_it_allows_calling_next_when_doctor_is_not_on_hold()
    {
        $response = $this->postJson('/api/queue/call-next', [
            'api_key' => 'test-api-key',
            'doctor_id' => $this->doctor->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.message', 'Next patient called successfully');
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
}
