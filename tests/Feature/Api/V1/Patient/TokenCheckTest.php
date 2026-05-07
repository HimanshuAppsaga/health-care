<?php

namespace Tests\Feature\Api\V1\Patient;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TokenCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_check_token_with_appointment_id(): void
    {
        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'api_key' => 'test-api-key',
        ]);
        $user = User::create([
            'name' => 'Dr. Test',
            'email' => 'drtest@example.com',
            'phone' => '0987654321',
            'password' => Hash::make('password'),
        ]);
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'clinic_id' => $clinic->id,
            'specialization' => 'General',
            'qualification' => 'MBBS',
            'experience_years' => 5,
            'consultation_fee' => 500,
        ]);
        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'dob' => '1990-01-01',
            'gender' => 'male',
        ]);
        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'phone' => '1234567890',
            'name' => 'John Doe',
            'token' => 'TK-123',
            'appointment_date' => now(),
            'status' => AppointmentStatus::PENDING,
        ]);

        $response = $this->postJson('/api/v1/patient/check-token', [
            'appointment_id' => $appointment->id,
        ], [
            'X-API-KEY' => 'test-api-key',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.message', 'Token found')
            ->assertJsonPath('data.appointment.id', $appointment->id);
    }

    public function test_it_returns_validation_error_if_appointment_id_is_missing(): void
    {
        $clinic = Clinic::create([
            'name' => 'Test Clinic',
            'api_key' => 'test-api-key',
        ]);

        $response = $this->postJson('/api/v1/patient/check-token', [], [
            'X-API-KEY' => 'test-api-key',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appointment_id']);
    }
}
