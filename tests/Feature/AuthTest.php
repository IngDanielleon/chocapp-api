<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    /** @test */
    public function user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Carlos Rodríguez',
            'email'                 => 'carlos@example.com',
            'password'              => 'Passw0rd!',
            'password_confirmation' => 'Passw0rd!',
            'id_type'               => 'CC',
            'id_number'             => '1234567890',
            'phone_number'          => '+573001234567',
            'terms_accepted'        => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'carlos@example.com']);
    }

    /** @test */
    public function user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Passw0rd!')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => $user->email,
            'password' => 'Passw0rd!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    /** @test */
    public function unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    /** @test */
    public function duplicate_email_registration_fails(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Duplicado',
            'email'                 => 'dup@example.com',
            'password'              => 'Passw0rd!',
            'password_confirmation' => 'Passw0rd!',
            'id_type'               => 'CC',
            'id_number'             => '9999999999',
            'phone_number'          => '+573009999999',
            'terms_accepted'        => true,
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function wrong_credentials_return_401(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_profile(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.email', $user->email);
    }

    /** @test */
    public function authenticated_user_can_logout(): void
    {
        $user  = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200);

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }
}
