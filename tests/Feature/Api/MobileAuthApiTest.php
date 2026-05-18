<?php

namespace Tests\Feature\Api;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileAuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;

    protected string $apiKey = 'test-mobile-api-key';

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test clinic
        $this->clinic = Clinic::create([
            'name' => 'Mobile Test Clinic',
            'api_key' => $this->apiKey,
        ]);

        // Ensure role exists
        Role::firstOrCreate(['name' => 'patient']);
    }

    /**
     * Test that API key validation works correctly.
     */
    public function test_api_key_middleware_validates_key(): void
    {
        // 1. Missing API Key
        $response = $this->postJson('/api/auth/login');
        $response->assertStatus(401)
            ->assertJsonPath('message', 'API key is required');

        // 2. Invalid API Key
        $response = $this->postJson('/api/auth/login', [], [
            'X-API-KEY' => 'invalid-key',
        ]);
        $response->assertStatus(403)
            ->assertJsonPath('message', 'Invalid API key');
    }

    /**
     * Test Login happy path and failure.
     */
    public function test_api_login(): void
    {
        $role = Role::where('name', 'patient')->first();

        $user = User::create([
            'name' => 'John Patient',
            'email' => 'john@example.com',
            'phone' => null,
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        // Success
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'auth' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role',
                        ],
                        'token',
                    ],
                ],
            ]);

        // Failure - incorrect password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', trans('auth.failed'));
    }

    /**
     * Test Register happy path and validation errors.
     */
    public function test_api_register(): void
    {
        // Success
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Alice New',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Registration successful')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'auth' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role',
                        ],
                        'token',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'name' => 'Alice New',
        ]);

        // Failure - duplicate email
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Alice Two',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('data.errors.email.0', 'The email has already been taken.');
    }

    /**
     * Test multiple registers without phone number succeed.
     */
    public function test_api_multiple_registers_without_phone_succeed(): void
    {
        // Register Alice
        $response1 = $this->postJson('/api/auth/register', [
            'name' => 'Alice New',
            'email' => 'alice@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response1->assertStatus(201);

        // Register Bob (which previously crashed due to unique empty phone constraint)
        $response2 = $this->postJson('/api/auth/register', [
            'name' => 'Bob New',
            'email' => 'bob@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response2->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'phone' => null,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'bob@example.com',
            'phone' => null,
        ]);
    }

    /**
     * Test Forgot Password.
     */
    public function test_api_forgot_password(): void
    {
        $role = Role::where('name', 'patient')->first();

        $user = User::create([
            'name' => 'John Patient',
            'email' => 'john@example.com',
            'phone' => null,
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'john@example.com',
        ], [
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(200);
    }
}
