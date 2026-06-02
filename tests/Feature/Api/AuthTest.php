<?php

namespace Tests\Feature\Api;

use App\Models\Clinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected Clinic $clinic;
    protected array $headers;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->clinic = Clinic::factory()->create();
        $this->headers = [
            'X-API-KEY' => $this->clinic->api_key,
            'Accept' => 'application/json',
        ];
    }

    public function test_mobile_login_success()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ], $this->headers);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'auth' => [
                             'user' => ['id', 'name', 'email'],
                             'token'
                         ]
                     ]
                 ]);
    }

    public function test_mobile_login_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ], $this->headers);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'data' => [
                         'errors' => ['email']
                     ]
                 ]);
    }

    public function test_mobile_register_success()
    {
        Role::factory()->create(['name' => 'patient']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '9876543210'
        ], $this->headers);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'auth' => [
                             'user' => ['id', 'name', 'email', 'phone'],
                             'token'
                         ]
                     ]
                 ]);
                 
        $this->assertDatabaseHas('users', [
            'email' => 'janedoe@example.com',
            'name' => 'Jane Doe'
        ]);
    }

    public function test_api_key_required()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'API key is required']);
    }

    public function test_mobile_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile-app')->plainTextToken;

        $headers = array_merge($this->headers, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response = $this->postJson('/api/auth/logout', [], $headers);

        $response->assertStatus(200);
                 
        $this->assertCount(0, $user->tokens);
    }
}
