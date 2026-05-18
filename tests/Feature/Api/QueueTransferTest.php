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

class QueueTransferTest extends TestCase
{
    use RefreshDatabase;

    protected $clinic;

    protected $doctor;

    protected $patient1;

    protected $patient2;

    protected $patient3;

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
            'transfer_depth' => 2, // Set transfer depth to 2
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

        // Create 3 patients
        for ($i = 1; $i <= 3; $i++) {
            $name = "Patient $i";
            $this->{"patient$i"} = Patient::create([
                'clinic_id' => $this->clinic->id,
                'name' => $name,
                'phone' => "987654321$i",
                'email' => "patient$i@example.com",
                'dob' => '1990-01-01',
                'gender' => 'male',
            ]);

            $appointment = Appointment::create([
                'clinic_id' => $this->clinic->id,
                'doctor_id' => $this->doctor->id,
                'patient_id' => $this->{"patient$i"}->id,
                'name' => $name,
                'phone' => "987654321$i",
                'appointment_date' => now()->format('Y-m-d'),
                'status' => AppointmentStatus::PENDING,
                'token' => (string) $i,
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'token_number' => (string) $i,
                'status' => $i === 1 ? QueueStatus::SERVING : QueueStatus::WAITING,
            ]);
        }
    }

    public function test_it_transfers_token_using_clinic_transfer_depth()
    {
        // Initially: Patient 1 (Serving, Token 1), Patient 2 (Waiting, Token 2), Patient 3 (Waiting, Token 3)
        // With transfer_depth = 2:
        // Patient 1 should move behind 2 people.
        // Patient 2 (Waiting) -> Token 1
        // Patient 3 (Waiting) -> Token 2
        // Patient 1 (Waiting) -> Token 3

        $response = $this->withHeaders(['x-api-key' => 'test-api-key'])
            ->postJson('/api/queue/transfer', [
            'doctor_id' => $this->doctor->id,
            // No transfer_count provided
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.message', 'Token successfully transferred behind 2 patients.');

        // Verify the tokens in database
        $this->assertEquals('1', Appointment::where('patient_id', $this->patient2->id)->first()->token);
        $this->assertEquals('2', Appointment::where('patient_id', $this->patient3->id)->first()->token);
        $this->assertEquals('3', Appointment::where('patient_id', $this->patient1->id)->first()->token);

        // Verify queue status (all should be waiting after transfer according to service logic)
        $this->assertEquals(QueueStatus::WAITING, Queue::where('appointment_id', Appointment::where('patient_id', $this->patient1->id)->first()->id)->first()->status);
    }

    public function test_it_uses_fallback_depth_if_not_set()
    {
        $this->clinic->update(['transfer_depth' => 0]);

        $response = $this->withHeaders(['x-api-key' => 'test-api-key'])
            ->postJson('/api/queue/transfer', [
            'doctor_id' => $this->doctor->id,
        ]);

        $response->assertStatus(200);
        // If depth is 0, we might want to default to 1 or just do nothing.
        // My implementation will default to 1.
        $response->assertJsonPath('data.message', 'Token successfully transferred behind 1 patients.');
    }
}
