<?php

namespace Tests\Feature;

use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use App\Services\TokenTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenTransferTest extends TestCase
{
    use RefreshDatabase;

    protected TokenTransferService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TokenTransferService;
    }

    public function test_it_transfers_token_by_skipping_people_correctly()
    {
        $clinic = Clinic::create(['name' => 'Test Clinic', 'transfer_depth' => 2]);
        $user = User::create([
            'name' => 'Dr. Smith',
            'email' => 'smith1@example.com',
            'password' => 'password',
            'phone' => '1111111111',
        ]);
        $doctor = Doctor::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'specialization' => 'General',
            'qualification' => 'MBBS',
            'experience_years' => 10,
            'consultation_fee' => 500,
        ]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'phone' => '1234567890',
        ]);
        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'user_id' => $patientUser->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
            'dob' => '1990-01-01',
            'gender' => 'male',
        ]);

        // Create 5 appointments with non-contiguous tokens: 10, 20, 30, 40, 50
        for ($i = 1; $i <= 5; $i++) {
            $token = (string) ($i * 10);
            $appointment = Appointment::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => now()->toDateString(),
                'token' => $token,
                'status' => 'pending',
                'name' => "Patient $i",
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'token_number' => $token,
                'status' => $i === 1 ? QueueStatus::SERVING : QueueStatus::WAITING,
            ]);
        }

        // Current is Patient 1 (Token 10).
        // Transfer by 2 people.
        // Waiting list is: Patient 2 (20), Patient 3 (30), Patient 4 (40), Patient 5 (50).
        // Skip 2 people (Patient 2, 3).
        // New position should be where Patient 3 was (Token 30).
        // Tokens used: 10, 20, 30.
        // New assignments:
        // Patient 2 -> 10
        // Patient 3 -> 20
        // Patient 1 -> 30

        $result = $this->service->transferToken($clinic->id, $doctor->id, 2);

        $this->assertTrue($result['success']);
        $this->assertEquals(30, $result['data']['new_token']);

        // Verify shifts
        $this->assertEquals('10', Appointment::where('name', 'Patient 2')->first()->token);
        $this->assertEquals('20', Appointment::where('name', 'Patient 3')->first()->token);
        $this->assertEquals('30', Appointment::where('name', 'Patient 1')->first()->token);
        $this->assertEquals('40', Appointment::where('name', 'Patient 4')->first()->token); // Unchanged
    }

    public function test_it_caps_transfer_to_last_person_in_queue()
    {
        $clinic = Clinic::create(['name' => 'Test Clinic']);
        $user = User::create([
            'name' => 'Dr. Smith',
            'email' => 'smith2@example.com',
            'password' => 'password',
            'phone' => '2222222222',
        ]);
        $doctor = Doctor::create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'specialization' => 'General',
            'qualification' => 'MBBS',
            'experience_years' => 10,
            'consultation_fee' => 500,
        ]);

        $patientUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'phone' => '0987654321',
        ]);
        $patient = Patient::create([
            'clinic_id' => $clinic->id,
            'user_id' => $patientUser->id,
            'name' => 'Jane Doe',
            'phone' => '0987654321',
            'dob' => '1995-05-05',
            'gender' => 'female',
        ]);

        // Create 3 appointments: 10, 20, 30. Serving 10.
        for ($i = 1; $i <= 3; $i++) {
            $token = (string) ($i * 10);
            $appointment = Appointment::create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_date' => now()->toDateString(),
                'token' => $token,
                'status' => 'pending',
                'name' => "Patient $i",
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'token_number' => $token,
                'status' => $i === 1 ? QueueStatus::SERVING : QueueStatus::WAITING,
            ]);
        }

        // Current 1, Transfer 10 people. Only 2 waiting (20, 30).
        // Skip all 2 people.
        // New assignments:
        // Patient 2 -> 10
        // Patient 3 -> 20
        // Patient 1 -> 30

        $result = $this->service->transferToken($clinic->id, $doctor->id, 10);

        $this->assertTrue($result['success']);
        $this->assertEquals(30, $result['data']['new_token']);

        $this->assertEquals('10', Appointment::where('name', 'Patient 2')->first()->token);
        $this->assertEquals('20', Appointment::where('name', 'Patient 3')->first()->token);
        $this->assertEquals('30', Appointment::where('name', 'Patient 1')->first()->token);
    }
}
