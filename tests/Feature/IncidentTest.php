<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    use RefreshDatabase;

    private User    $user;
    private Vehicle $vehicle;
    private string  $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');

        $this->user    = User::factory()->create();
        $this->vehicle = Vehicle::factory()->create(['user_id' => $this->user->id]);
        $this->token   = $this->user->createToken('test')->plainTextToken;
    }

    /** @test */
    public function authenticated_user_can_create_incident_with_photos(): void
    {
        $photos = [];
        foreach (['FRONT', 'REAR', 'LEFT', 'RIGHT'] as $angle) {
            $photos[] = [
                'file'  => UploadedFile::fake()->image("{$angle}.jpg", 800, 600),
                'angle' => $angle,
            ];
        }

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/incidents', [
                'vehicle_id'        => $this->vehicle->id,
                'description'       => 'Colisión en la Calle 72 con Carrera 11, Bogotá.',
                'incident_date'     => today()->toDateString(),
                'incident_time'     => '14:30',
                'location_address'  => 'Calle 72 # 11-10, Bogotá',
                'latitude'          => 4.6800,
                'longitude'         => -74.0560,
                'weather_condition' => 'LLUVIOSO',
                'road_condition'    => 'HUMEDO',
                'photos'            => $photos,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['id', 'status', 'cover_photo_url'],
            ]);

        $this->assertDatabaseHas('incidents', [
            'user_id'    => $this->user->id,
            'vehicle_id' => $this->vehicle->id,
        ]);

        $this->assertDatabaseCount('incident_photos', 4);
    }

    /** @test */
    public function incident_requires_minimum_4_photos(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/incidents', [
                'vehicle_id'        => $this->vehicle->id,
                'description'       => 'Descripción del accidente con suficiente texto.',
                'incident_date'     => today()->toDateString(),
                'incident_time'     => '10:00',
                'latitude'          => 4.6800,
                'longitude'         => -74.0560,
                'weather_condition' => 'SOLEADO',
                'road_condition'    => 'BUEN_ESTADO',
                'photos'            => [
                    ['file' => UploadedFile::fake()->image('f.jpg'), 'angle' => 'FRONT'],
                    ['file' => UploadedFile::fake()->image('r.jpg'), 'angle' => 'REAR'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['photos']);
    }

    /** @test */
    public function user_cannot_view_another_users_incident(): void
    {
        $other         = User::factory()->create();
        $otherVehicle  = Vehicle::factory()->create(['user_id' => $other->id]);
        $otherIncident = Incident::factory()->create([
            'user_id'    => $other->id,
            'vehicle_id' => $otherVehicle->id,
        ]);

        $this->withToken($this->token)
            ->getJson("/api/v1/incidents/{$otherIncident->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_incidents(): void
    {
        $this->getJson('/api/v1/incidents')->assertStatus(401);
    }

    /** @test */
    public function document_status_accessor_returns_correct_values(): void
    {
        $expired = \App\Models\Document::factory()->create([
            'vehicle_id'  => $this->vehicle->id,
            'type'        => 'SOAT',
            'expiry_date' => now()->subDay()->toDateString(),
        ]);
        $soon = \App\Models\Document::factory()->create([
            'vehicle_id'  => $this->vehicle->id,
            'type'        => 'TECNOMECANICA',
            'expiry_date' => now()->addDays(15)->toDateString(),
        ]);
        $valid = \App\Models\Document::factory()->create([
            'vehicle_id'  => $this->vehicle->id,
            'type'        => 'LICENCIA',
            'expiry_date' => now()->addDays(120)->toDateString(),
        ]);

        $this->assertEquals('VENCIDO',      $expired->status);
        $this->assertEquals('VENCE_PRONTO', $soon->status);
        $this->assertEquals('VIGENTE',      $valid->status);
    }
}
