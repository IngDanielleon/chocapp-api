# ChocApp — Technical Specification
## Laravel API Backend

> **Versión:** 1.0.0  
> **Fecha:** 2026-05-30  
> **Stack:** Laravel 11 · PHP 8.3 · MySQL 8 · Redis · Nginx · Docker  

---

## Tabla de Contenidos

1. [Modelo de Base de Datos](#1-modelo-de-base-de-datos)
2. [Estructura del Proyecto](#2-estructura-del-proyecto)
3. [Modelos Eloquent](#3-modelos-eloquent)
4. [Enums y DTOs](#4-enums-y-dtos)
5. [Repositorios y Servicios](#5-repositorios-y-servicios)
6. [Form Requests](#6-form-requests)
7. [Controladores con Swagger](#7-controladores-con-swagger)
8. [API Resources](#8-api-resources)
9. [Policies](#9-policies)
10. [Comandos de Consola y Schedule](#10-comandos-de-consola-y-schedule)
11. [Middleware](#11-middleware)
12. [Rutas (api.php)](#12-rutas-apiphp)
13. [Template PDF](#13-template-pdf)
14. [Docker Compose](#14-docker-compose)
15. [Dockerfile](#15-dockerfile)
16. [Nginx Config](#16-nginx-config)
17. [Jenkinsfile](#17-jenkinsfile)
18. [MCP — Model Context Protocol](#18-mcp--model-context-protocol)
19. [Tests](#19-tests)
20. [Variables de Entorno y README](#20-variables-de-entorno-y-readme)

---

## 1. Modelo de Base de Datos

### Comandos de Scaffold Artisan

```bash
# Migrations
php artisan make:migration create_users_table
php artisan make:migration create_vehicles_table
php artisan make:migration create_documents_table
php artisan make:migration create_incidents_table
php artisan make:migration create_incident_photos_table
php artisan make:migration create_third_parties_table
php artisan make:migration create_maintenance_records_table
php artisan make:migration create_notifications_table

# Models
php artisan make:model User
php artisan make:model Vehicle
php artisan make:model Document
php artisan make:model Incident
php artisan make:model IncidentPhoto
php artisan make:model ThirdParty
php artisan make:model MaintenanceRecord
php artisan make:model Notification

# Controllers
php artisan make:controller Api/AuthController --api
php artisan make:controller Api/VehicleController --api
php artisan make:controller Api/DocumentController --api
php artisan make:controller Api/IncidentController --api
php artisan make:controller Api/MaintenanceController --api
php artisan make:controller Api/NotificationController --api
php artisan make:controller Api/SupportController

# Requests
php artisan make:request Auth/RegisterRequest
php artisan make:request Auth/LoginRequest
php artisan make:request Auth/SocialLoginRequest
php artisan make:request Incident/CreateIncidentRequest
php artisan make:request Incident/UpdateIncidentRequest
php artisan make:request Vehicle/CreateVehicleRequest
php artisan make:request Document/UpsertDocumentRequest

# Resources
php artisan make:resource UserResource
php artisan make:resource VehicleResource
php artisan make:resource DocumentResource
php artisan make:resource IncidentResource
php artisan make:resource IncidentDetailResource
php artisan make:resource MaintenanceResource
php artisan make:resource NotificationResource

# Policies
php artisan make:policy IncidentPolicy --model=Incident
php artisan make:policy VehiclePolicy --model=Vehicle
php artisan make:policy DocumentPolicy --model=Document

# Console Commands
php artisan make:command SendDocumentExpiryAlerts
php artisan make:command SendMaintenanceReminders

# Events & Listeners
php artisan make:event IncidentCreated
php artisan make:listener NotifyInsuranceOnIncident --event=IncidentCreated

# Middleware
php artisan make:middleware ForceJsonResponse
php artisan make:middleware SecurityHeaders
php artisan make:middleware RateLimitByUser
```

---

### Tabla: `users`

```sql
CREATE TABLE users (
    id               CHAR(36)     PRIMARY KEY,
    name             VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL,
    password         VARCHAR(255) NOT NULL,
    id_type          ENUM('CC','CE','PPT','PASAPORTE') NOT NULL,
    id_number        VARCHAR(30)  NOT NULL,
    phone_number     VARCHAR(20)  NOT NULL,
    profile_pic_url  VARCHAR(500) NULL,
    terms_accepted   TINYINT(1)   NOT NULL DEFAULT 0,
    social_provider  VARCHAR(20)  NULL,
    social_id        VARCHAR(255) NULL,
    fcm_token        VARCHAR(500) NULL,
    created_at       TIMESTAMP    NULL,
    updated_at       TIMESTAMP    NULL,
    deleted_at       TIMESTAMP    NULL,

    UNIQUE  INDEX idx_users_email      (email),
    UNIQUE  INDEX idx_users_id_number  (id_number),
            INDEX idx_users_social     (social_provider, social_id)
);
```

### Tabla: `vehicles`

```sql
CREATE TABLE vehicles (
    id         CHAR(36)    PRIMARY KEY,
    user_id    CHAR(36)    NOT NULL,
    plate      VARCHAR(10) NOT NULL,
    brand      VARCHAR(60) NOT NULL,
    model      VARCHAR(60) NOT NULL,
    year       SMALLINT UNSIGNED NOT NULL,
    color      VARCHAR(40) NOT NULL,
    type       ENUM('MOTOCICLETA','AUTOMOVIL') NOT NULL DEFAULT 'AUTOMOVIL',
    photo_url  VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    UNIQUE  INDEX idx_vehicles_plate   (plate),
            INDEX idx_vehicles_user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Tabla: `documents`

```sql
CREATE TABLE documents (
    id              CHAR(36)    PRIMARY KEY,
    vehicle_id      CHAR(36)    NOT NULL,
    type            ENUM('SOAT','TECNOMECANICA','LICENCIA') NOT NULL,
    document_number VARCHAR(60) NOT NULL,
    issue_date      DATE        NULL,
    expiry_date     DATE        NOT NULL,
    pdf_url         VARCHAR(500) NULL,
    notes           TEXT        NULL,
    created_at      TIMESTAMP   NULL,
    updated_at      TIMESTAMP   NULL,

    -- status es un Accessor en el modelo (VIGENTE | VENCE_PRONTO | VENCIDO)
    -- NO es columna de base de datos

    UNIQUE  INDEX idx_documents_vehicle_type (vehicle_id, type),
            INDEX idx_documents_vehicle_id   (vehicle_id),
            INDEX idx_documents_expiry_date  (expiry_date),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
```

### Tabla: `incidents`

```sql
CREATE TABLE incidents (
    id                   CHAR(36)      PRIMARY KEY,
    user_id              CHAR(36)      NOT NULL,
    vehicle_id           CHAR(36)      NOT NULL,
    title                VARCHAR(200)  NOT NULL,
    description          TEXT          NOT NULL,
    incident_date        DATE          NOT NULL,
    incident_time        TIME          NOT NULL,
    location_address     VARCHAR(500)  NOT NULL,
    latitude             DECIMAL(10,8) NOT NULL,
    longitude            DECIMAL(11,8) NOT NULL,
    weather_condition    ENUM('SOLEADO','LLUVIOSO','NUBLADO','NOCHE') NOT NULL,
    road_condition       ENUM('BUEN_ESTADO','HUMEDO','HUECOS','DERRUMBE') NOT NULL,
    police_report_number VARCHAR(60)   NULL,
    status               ENUM('BORRADOR','REPORTADO','EN_REVISION','FINALIZADO')
                         NOT NULL DEFAULT 'BORRADOR',
    report_pdf_url       VARCHAR(500)  NULL,
    created_at           TIMESTAMP     NULL,
    updated_at           TIMESTAMP     NULL,
    deleted_at           TIMESTAMP     NULL,

    INDEX idx_incidents_user_id    (user_id),
    INDEX idx_incidents_vehicle_id (vehicle_id),
    INDEX idx_incidents_status     (status),
    INDEX idx_incidents_date       (incident_date),
    FOREIGN KEY (user_id)    REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);
```

### Tabla: `incident_photos`

```sql
CREATE TABLE incident_photos (
    id          CHAR(36) PRIMARY KEY,
    incident_id CHAR(36) NOT NULL,
    angle       ENUM('FRONT','FRONT_RIGHT','RIGHT','REAR_RIGHT',
                     'REAR','REAR_LEFT','LEFT','FRONT_LEFT',
                     'INTERIOR','ODOMETER','EXTRA') NOT NULL,
    image_url   VARCHAR(500) NOT NULL,
    taken_at    TIMESTAMP NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,

    INDEX idx_incident_photos_incident_id (incident_id),
    INDEX idx_incident_photos_angle       (incident_id, angle),
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE
);
```

### Tabla: `third_parties`

```sql
CREATE TABLE third_parties (
    id                CHAR(36)    PRIMARY KEY,
    incident_id       CHAR(36)    NOT NULL,
    party_type        ENUM('VEHICULO','PEATON','CICLISTA') NOT NULL,
    plate             VARCHAR(10) NULL,
    brand             VARCHAR(60) NULL,
    model             VARCHAR(60) NULL,
    color             VARCHAR(40) NULL,
    driver_name       VARCHAR(100) NULL,
    driver_id         VARCHAR(30)  NULL,
    driver_phone      VARCHAR(20)  NULL,
    insurance_company VARCHAR(100) NULL,
    insurance_policy  VARCHAR(60)  NULL,
    created_at        TIMESTAMP NULL,
    updated_at        TIMESTAMP NULL,

    INDEX idx_third_parties_incident_id (incident_id),
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE
);
```

### Tabla: `maintenance_records`

```sql
CREATE TABLE maintenance_records (
    id               CHAR(36)      PRIMARY KEY,
    vehicle_id       CHAR(36)      NOT NULL,
    maintenance_date DATE          NOT NULL,
    type             ENUM('ACEITE','FRENOS','LLANTAS','BATERIA',
                         'FILTROS','SUSPENSION','REVISION_GENERAL','OTRO') NOT NULL,
    cost             DECIMAL(12,2) NULL,
    workshop_name    VARCHAR(150)  NULL,
    current_mileage  INT UNSIGNED  NULL,
    notes            TEXT          NULL,
    next_date        DATE          NULL,
    next_mileage     INT UNSIGNED  NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,

    INDEX idx_maintenance_vehicle_id (vehicle_id),
    INDEX idx_maintenance_date       (maintenance_date),
    INDEX idx_maintenance_type       (vehicle_id, type),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
```

### Tabla: `notifications`

```sql
CREATE TABLE notifications (
    id         CHAR(36)     PRIMARY KEY,
    user_id    CHAR(36)     NOT NULL,
    title      VARCHAR(200) NOT NULL,
    body       TEXT         NOT NULL,
    type       ENUM('ALERT','INFO','DOCUMENT_EXPIRING',
                   'MAINTENANCE_REMINDER','INCIDENT_UPDATE') NOT NULL,
    data       JSON         NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    read_at    TIMESTAMP    NULL,
    created_at TIMESTAMP    NULL,
    updated_at TIMESTAMP    NULL,

    INDEX idx_notifications_user_id (user_id),
    INDEX idx_notifications_is_read (user_id, is_read),
    INDEX idx_notifications_type    (type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## 2. Estructura del Proyecto

```
chocapp/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── SendDocumentExpiryAlerts.php
│   │       └── SendMaintenanceReminders.php
│   ├── DTOs/
│   │   ├── Auth/RegisterDTO.php
│   │   ├── Incident/CreateIncidentDTO.php
│   │   └── Vehicle/CreateVehicleDTO.php
│   ├── Enums/
│   │   ├── DocumentTypeEnum.php
│   │   ├── IncidentStatusEnum.php
│   │   ├── VehicleTypeEnum.php
│   │   └── NotificationTypeEnum.php
│   ├── Events/
│   │   └── IncidentCreated.php
│   ├── Exceptions/
│   │   ├── Handler.php
│   │   ├── ApiException.php
│   │   └── UnauthorizedException.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── VehicleController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── IncidentController.php
│   │   │   ├── MaintenanceController.php
│   │   │   ├── NotificationController.php
│   │   │   └── SupportController.php
│   │   ├── Middleware/
│   │   │   ├── ForceJsonResponse.php
│   │   │   ├── SecurityHeaders.php
│   │   │   └── RateLimitByUser.php
│   │   ├── Requests/
│   │   │   ├── Auth/RegisterRequest.php
│   │   │   ├── Auth/LoginRequest.php
│   │   │   ├── Auth/SocialLoginRequest.php
│   │   │   ├── Incident/CreateIncidentRequest.php
│   │   │   ├── Incident/UpdateIncidentRequest.php
│   │   │   ├── Vehicle/CreateVehicleRequest.php
│   │   │   └── Document/UpsertDocumentRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── VehicleResource.php
│   │       ├── DocumentResource.php
│   │       ├── IncidentResource.php
│   │       ├── IncidentDetailResource.php
│   │       ├── MaintenanceResource.php
│   │       └── NotificationResource.php
│   ├── Listeners/
│   │   └── NotifyInsuranceOnIncident.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Vehicle.php
│   │   ├── Document.php
│   │   ├── Incident.php
│   │   ├── IncidentPhoto.php
│   │   ├── ThirdParty.php
│   │   ├── MaintenanceRecord.php
│   │   └── Notification.php
│   ├── Policies/
│   │   ├── IncidentPolicy.php
│   │   ├── VehiclePolicy.php
│   │   └── DocumentPolicy.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── IncidentRepositoryInterface.php
│   │   │   └── UserRepositoryInterface.php
│   │   ├── IncidentRepository.php
│   │   └── UserRepository.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── IncidentService.php
│   │   ├── DocumentStatusService.php
│   │   ├── PdfReportService.php
│   │   ├── FcmNotificationService.php
│   │   ├── StorageService.php
│   │   └── SocialAuthService.php
│   └── Traits/
│       └── ApiResponseTrait.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   └── api.php
├── resources/
│   └── views/pdf/
│       └── incident-report.blade.php
├── docker-files/
│   ├── nginx/site.conf
│   ├── php/php.ini
│   └── nodejs/start.sh
├── mcp/
│   └── chocapp-api.json
├── docker-compose.yml
├── Dockerfile
└── Jenkinsfile
```

---

## 3. Modelos Eloquent

### User.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'name', 'email', 'password', 'id_type', 'id_number',
        'phone_number', 'profile_pic_url', 'terms_accepted',
        'social_provider', 'social_id', 'fcm_token',
    ];

    protected $hidden = ['password', 'remember_token', 'social_id', 'fcm_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'terms_accepted'    => 'boolean',
        'password'          => 'hashed',
    ];

    /** @return HasMany<Vehicle> */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    /** @return HasMany<Incident> */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /** @return HasMany<Notification> */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
```

### Document.php (con Accessor de status)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'vehicle_id', 'type', 'document_number',
        'issue_date', 'expiry_date', 'pdf_url', 'notes',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    protected $appends = ['status'];

    /**
     * Computed document status — NOT stored in DB.
     * Returns: VIGENTE | VENCE_PRONTO | VENCIDO
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $today = now()->toDateString();
                $soon  = now()->addDays(30)->toDateString();

                if ($this->expiry_date->toDateString() < $today) {
                    return 'VENCIDO';
                }
                if ($this->expiry_date->toDateString() <= $soon) {
                    return 'VENCE_PRONTO';
                }
                return 'VIGENTE';
            }
        );
    }

    /** @return BelongsTo<Vehicle, Document> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Scope: documents expiring within N days */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereBetween('expiry_date', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    /** Scope: already expired documents */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }
}
```

### Incident.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use App\Enums\IncidentStatusEnum;

class Incident extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'user_id', 'vehicle_id', 'title', 'description',
        'incident_date', 'incident_time', 'location_address',
        'latitude', 'longitude', 'weather_condition', 'road_condition',
        'police_report_number', 'status', 'report_pdf_url',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'status'        => IncidentStatusEnum::class,
        'latitude'      => 'decimal:8',
        'longitude'     => 'decimal:8',
    ];

    protected $appends = ['cover_photo_url'];

    /** First available photo URL (FRONT preferred) */
    protected function coverPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $front = $this->photos()
                    ->where('angle', 'FRONT')
                    ->first();
                return $front?->image_url ?? $this->photos()->first()?->image_url;
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(IncidentPhoto::class);
    }

    public function thirdParties(): HasMany
    {
        return $this->hasMany(ThirdParty::class);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

---

## 4. Enums y DTOs

### IncidentStatusEnum.php

```php
<?php

namespace App\Enums;

enum IncidentStatusEnum: string
{
    case BORRADOR    = 'BORRADOR';
    case REPORTADO   = 'REPORTADO';
    case EN_REVISION = 'EN_REVISION';
    case FINALIZADO  = 'FINALIZADO';

    public function label(): string
    {
        return match($this) {
            self::BORRADOR    => 'Borrador',
            self::REPORTADO   => 'Reportado',
            self::EN_REVISION => 'En revisión',
            self::FINALIZADO  => 'Finalizado',
        };
    }
}
```

### DocumentTypeEnum.php

```php
<?php

namespace App\Enums;

enum DocumentTypeEnum: string
{
    case SOAT          = 'SOAT';
    case TECNOMECANICA = 'TECNOMECANICA';
    case LICENCIA      = 'LICENCIA';
}
```

### CreateIncidentDTO.php

```php
<?php

namespace App\DTOs\Incident;

readonly class CreateIncidentDTO
{
    public function __construct(
        public string $vehicleId,
        public string $title,
        public string $description,
        public string $incidentDate,
        public string $incidentTime,
        public string $locationAddress,
        public float  $latitude,
        public float  $longitude,
        public string $weatherCondition,
        public string $roadCondition,
        public ?string $policeReportNumber = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            vehicleId:           $data['vehicle_id'],
            title:               $data['title'] ?? 'Accidente ' . now()->format('d/m/Y'),
            description:         $data['description'],
            incidentDate:        $data['incident_date'],
            incidentTime:        $data['incident_time'],
            locationAddress:     $data['location_address'],
            latitude:            (float) $data['latitude'],
            longitude:           (float) $data['longitude'],
            weatherCondition:    $data['weather_condition'],
            roadCondition:       $data['road_condition'],
            policeReportNumber:  $data['police_report_number'] ?? null,
        );
    }
}
```

---

## 5. Repositorios y Servicios

### IncidentRepositoryInterface.php

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\{Incident, User};
use Illuminate\Pagination\LengthAwarePaginator;

interface IncidentRepositoryInterface
{
    public function findByUser(User $user, array $filters = []): LengthAwarePaginator;
    public function findById(string $id): ?Incident;
    public function create(array $data): Incident;
    public function update(Incident $incident, array $data): Incident;
    public function delete(Incident $incident): bool;
}
```

### IncidentService.php

```php
<?php

namespace App\Services;

use App\DTOs\Incident\CreateIncidentDTO;
use App\Enums\IncidentStatusEnum;
use App\Events\IncidentCreated;
use App\Models\{Incident, User};
use App\Repositories\Contracts\IncidentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncidentService
{
    public function __construct(
        private readonly IncidentRepositoryInterface $repository,
        private readonly StorageService $storageService,
    ) {}

    /**
     * Create a new incident with photos.
     *
     * @param  CreateIncidentDTO  $dto
     * @param  array<array{file: UploadedFile, angle: string}>  $photos
     * @param  User  $user
     */
    public function create(CreateIncidentDTO $dto, array $photos, User $user): Incident
    {
        return DB::transaction(function () use ($dto, $photos, $user): Incident {
            $incident = $this->repository->create([
                'user_id'              => $user->id,
                'vehicle_id'           => $dto->vehicleId,
                'title'                => $dto->title,
                'description'          => $dto->description,
                'incident_date'        => $dto->incidentDate,
                'incident_time'        => $dto->incidentTime,
                'location_address'     => $dto->locationAddress,
                'latitude'             => $dto->latitude,
                'longitude'            => $dto->longitude,
                'weather_condition'    => $dto->weatherCondition,
                'road_condition'       => $dto->roadCondition,
                'police_report_number' => $dto->policeReportNumber,
                'status'               => IncidentStatusEnum::REPORTADO->value,
            ]);

            $this->uploadPhotos($incident, $photos);

            event(new IncidentCreated($incident));

            return $incident->load(['photos', 'thirdParties', 'vehicle']);
        });
    }

    /**
     * @param  array<array{file: UploadedFile, angle: string}>  $photos
     */
    public function uploadPhotos(Incident $incident, array $photos): void
    {
        foreach ($photos as $photoData) {
            try {
                $url = $this->storageService->uploadFile(
                    $photoData['file'],
                    "incidents/{$incident->id}/photos"
                );

                $incident->photos()->create([
                    'angle'     => strtoupper($photoData['angle']),
                    'image_url' => $url,
                    'taken_at'  => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('ChocApp: Error uploading incident photo', [
                    'incident_id' => $incident->id,
                    'angle'       => $photoData['angle'],
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    public function updateStatus(Incident $incident, IncidentStatusEnum $status): Incident
    {
        $incident->update(['status' => $status->value]);
        return $incident->fresh();
    }

    public function deleteWithPhotos(Incident $incident): void
    {
        DB::transaction(function () use ($incident): void {
            foreach ($incident->photos as $photo) {
                $this->storageService->deleteFile($photo->image_url);
            }
            $incident->photos()->delete();
            $incident->thirdParties()->delete();
            $incident->delete();
        });
    }
}
```

### StorageService.php

```php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 's3');
    }

    public function uploadFile(UploadedFile $file, string $path): string
    {
        // Rename with UUID — never trust original filename
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath  = "{$path}/{$filename}";

        Storage::disk($this->disk)->put($fullPath, file_get_contents($file));

        return Storage::disk($this->disk)->url($fullPath);
    }

    public function deleteFile(string $url): void
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            Storage::disk($this->disk)->delete(ltrim($path, '/'));
        }
    }

    public function generateSignedUrl(string $path, int $minutes = 60): string
    {
        return Storage::disk($this->disk)
            ->temporaryUrl($path, now()->addMinutes($minutes));
    }
}
```

### FcmNotificationService.php

```php
<?php

namespace App\Services;

use App\Models\{Notification, User};
use Illuminate\Support\Facades\{Http, Log};

class FcmNotificationService
{
    private string $projectId;
    private string $serverKey;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id');
        $this->serverKey = config('services.fcm.server_key');
    }

    public function send(User $user, string $title, string $body, array $data = []): void
    {
        // Persist notification record regardless of push result
        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $data['type'] ?? 'INFO',
            'data'    => $data,
        ]);

        if (empty($user->fcm_token)) {
            return;
        }

        try {
            Http::withToken($this->serverKey)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                    'message' => [
                        'token'        => $user->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => array_map('strval', $data),
                    ],
                ]);
        } catch (\Throwable $e) {
            // Silently log — never break the main flow
            Log::warning('ChocApp: FCM push failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
```

### PdfReportService.php

```php
<?php

namespace App\Services;

use App\Models\Incident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PdfReportService
{
    public function __construct(
        private readonly StorageService $storageService,
    ) {}

    public function generate(Incident $incident): string
    {
        // Return cached URL if already generated
        if ($incident->report_pdf_url) {
            return $incident->report_pdf_url;
        }

        $incident->load(['photos', 'thirdParties', 'vehicle', 'user']);

        $pdf = Pdf::loadView('pdf.incident-report', [
            'incident'     => $incident,
            'generatedAt'  => now()->format('d/m/Y H:i:s'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        $tmpPath  = sys_get_temp_dir() . '/' . Str::uuid() . '.pdf';
        file_put_contents($tmpPath, $pdf->output());

        $url = $this->storageService->uploadFile(
            new \Illuminate\Http\File($tmpPath),
            "incidents/{$incident->id}/reports"
        );

        @unlink($tmpPath);

        $incident->update(['report_pdf_url' => $url]);

        return $url;
    }
}
```

### ApiResponseTrait.php

```php
<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed  $data    = null,
        string $message = 'Operación exitosa',
        int    $status  = 200,
        ?LengthAwarePaginator $paginator = null
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if ($paginator) {
            $response['meta'] = [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ];
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(
        string $message = 'Ha ocurrido un error',
        int    $status  = 400,
        mixed  $errors  = null,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'code'    => $status,
        ], $status);
    }

    protected function createdResponse(mixed $data, string $message = 'Creado exitosamente'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
```

---

## 6. Form Requests

### CreateIncidentRequest.php

```php
<?php

namespace App\Http\Requests\Incident;

use Illuminate\Foundation\Http\FormRequest;

class CreateIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'            => 'required|uuid|exists:vehicles,id',
            'description'           => 'required|string|min:10|max:2000',
            'incident_date'         => 'required|date|before_or_equal:today',
            'incident_time'         => 'required|date_format:H:i',
            'location_address'      => 'required|string|max:500',
            'latitude'              => 'required|numeric|between:-90,90',
            'longitude'             => 'required|numeric|between:-180,180',
            'weather_condition'     => 'required|in:SOLEADO,LLUVIOSO,NUBLADO,NOCHE',
            'road_condition'        => 'required|in:BUEN_ESTADO,HUMEDO,HUECOS,DERRUMBE',
            'police_report_number'  => 'nullable|string|max:60',
            'photos'                => 'required|array|min:4',
            'photos.*.file'         => 'required|image|mimes:jpeg,png,webp|max:10240',
            'photos.*.angle'        => 'required|in:FRONT,FRONT_RIGHT,RIGHT,REAR_RIGHT,
                                        REAR,REAR_LEFT,LEFT,FRONT_LEFT,INTERIOR,
                                        ODOMETER,EXTRA',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $vehicle = \App\Models\Vehicle::find($this->vehicle_id);
            if ($vehicle && $vehicle->user_id !== auth()->id()) {
                $v->errors()->add(
                    'vehicle_id',
                    'El vehículo no pertenece al usuario autenticado.'
                );
            }
        });
    }

    public function attributes(): array
    {
        return [
            'vehicle_id'       => 'vehículo',
            'description'      => 'descripción',
            'incident_date'    => 'fecha del incidente',
            'incident_time'    => 'hora del incidente',
            'location_address' => 'dirección',
            'latitude'         => 'latitud',
            'longitude'        => 'longitud',
            'photos'           => 'fotografías',
            'photos.*.file'    => 'archivo de fotografía',
            'photos.*.angle'   => 'ángulo de fotografía',
        ];
    }
}
```

### RegisterRequest.php

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|min:3|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'id_type'       => 'required|in:CC,CE,PPT,PASAPORTE',
            'id_number'     => 'required|string|unique:users,id_number',
            'phone_number'  => 'required|string|max:20',
            'terms_accepted'=> 'required|accepted',
            'profile_pic'   => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'           => 'nombre completo',
            'email'          => 'correo electrónico',
            'password'       => 'contraseña',
            'id_type'        => 'tipo de documento',
            'id_number'      => 'número de documento',
            'phone_number'   => 'número de teléfono',
            'terms_accepted' => 'términos y condiciones',
        ];
    }
}
```

---

## 7. Controladores con Swagger

### IncidentController.php

```php
<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Incident\CreateIncidentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Incident\CreateIncidentRequest;
use App\Http\Requests\Incident\UpdateIncidentRequest;
use App\Http\Resources\{IncidentResource, IncidentDetailResource};
use App\Models\Incident;
use App\Services\{IncidentService, PdfReportService};
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Incidents", description="Gestión de accidentes de tránsito")
 */
class IncidentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly IncidentService  $incidentService,
        private readonly PdfReportService $pdfService,
    ) {}

    /**
     * @OA\Get(
     *   path="/api/v1/incidents",
     *   tags={"Incidents"},
     *   summary="Listar accidentes del usuario autenticado",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="status", in="query", required=false,
     *     @OA\Schema(type="string", enum={"BORRADOR","REPORTADO","EN_REVISION","FINALIZADO"})
     *   ),
     *   @OA\Parameter(name="per_page", in="query", required=false,
     *     @OA\Schema(type="integer", default=15)
     *   ),
     *   @OA\Response(response=200, description="Lista paginada de incidentes",
     *     @OA\JsonContent(ref="#/components/schemas/ApiSuccessResponse")
     *   ),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Incident::forUser(auth()->id())
            ->with(['vehicle', 'photos'])
            ->latest('incident_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $incidents = $query->paginate($request->get('per_page', 15));

        return $this->successResponse(
            IncidentResource::collection($incidents),
            'Incidentes obtenidos exitosamente',
            200,
            $incidents
        );
    }

    /**
     * @OA\Post(
     *   path="/api/v1/incidents",
     *   tags={"Incidents"},
     *   summary="Registrar un nuevo accidente con fotos",
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"vehicle_id","description","incident_date","incident_time",
     *                   "latitude","longitude","weather_condition","road_condition","photos"},
     *         @OA\Property(property="vehicle_id", type="string", format="uuid"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="incident_date", type="string", format="date"),
     *         @OA\Property(property="incident_time", type="string", example="14:30"),
     *         @OA\Property(property="latitude",  type="number", format="float"),
     *         @OA\Property(property="longitude", type="number", format="float"),
     *         @OA\Property(property="weather_condition", type="string",
     *           enum={"SOLEADO","LLUVIOSO","NUBLADO","NOCHE"}),
     *         @OA\Property(property="road_condition", type="string",
     *           enum={"BUEN_ESTADO","HUMEDO","HUECOS","DERRUMBE"}),
     *         @OA\Property(property="photos", type="array",
     *           @OA\Items(
     *             @OA\Property(property="file",  type="string", format="binary"),
     *             @OA\Property(property="angle", type="string")
     *           )
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Incidente creado"),
     *   @OA\Response(response=422, description="Error de validación"),
     *   @OA\Response(response=429, description="Demasiadas solicitudes")
     * )
     */
    public function store(CreateIncidentRequest $request): JsonResponse
    {
        $incident = $this->incidentService->create(
            CreateIncidentDTO::fromRequest($request->validated()),
            $request->input('photos', []),
            auth()->user()
        );

        return $this->createdResponse(
            new IncidentDetailResource($incident),
            'Accidente registrado exitosamente'
        );
    }

    /**
     * @OA\Get(
     *   path="/api/v1/incidents/{id}",
     *   tags={"Incidents"},
     *   summary="Detalle completo de un accidente",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="string", format="uuid")
     *   ),
     *   @OA\Response(response=200, description="Detalle del incidente"),
     *   @OA\Response(response=403, description="Acceso denegado"),
     *   @OA\Response(response=404, description="No encontrado")
     * )
     */
    public function show(Incident $incident): JsonResponse
    {
        $this->authorize('view', $incident);

        $incident->load(['photos', 'thirdParties', 'vehicle', 'user']);

        return $this->successResponse(
            new IncidentDetailResource($incident),
            'Incidente obtenido exitosamente'
        );
    }

    /**
     * @OA\Get(
     *   path="/api/v1/incidents/{id}/export-pdf",
     *   tags={"Incidents"},
     *   summary="Generar y descargar reporte PDF oficial",
     *   security={{"BearerAuth":{}}},
     *   @OA\Parameter(name="id", in="path", required=true,
     *     @OA\Schema(type="string", format="uuid")
     *   ),
     *   @OA\Response(response=200, description="Archivo PDF",
     *     @OA\MediaType(mediaType="application/pdf")
     *   )
     * )
     */
    public function exportPdf(Incident $incident): Response
    {
        $this->authorize('view', $incident);

        $url = $this->pdfService->generate($incident);

        return response()->streamDownload(function () use ($url) {
            echo file_get_contents($url);
        }, "reporte-accidente-{$incident->id}.pdf", [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function destroy(Incident $incident): JsonResponse
    {
        $this->authorize('delete', $incident);
        $this->incidentService->deleteWithPhotos($incident);

        return $this->noContentResponse();
    }
}
```

### AuthController.php

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest, SocialLoginRequest};
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Auth", description="Autenticación de usuarios")
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly AuthService $authService) {}

    /**
     * @OA\Post(
     *   path="/api/v1/auth/register",
     *   tags={"Auth"},
     *   summary="Registrar nuevo usuario",
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(required={"name","email","password","id_type",
     *                            "id_number","phone_number","terms_accepted"},
     *         @OA\Property(property="name",          type="string"),
     *         @OA\Property(property="email",         type="string", format="email"),
     *         @OA\Property(property="password",      type="string", minLength=8),
     *         @OA\Property(property="id_type",       type="string",
     *           enum={"CC","CE","PPT","PASAPORTE"}),
     *         @OA\Property(property="id_number",     type="string"),
     *         @OA\Property(property="phone_number",  type="string"),
     *         @OA\Property(property="terms_accepted",type="boolean"),
     *         @OA\Property(property="profile_pic",   type="string", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Usuario registrado"),
     *   @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->register($request->validated(), $request->file('profile_pic'));

        return $this->createdResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Registro exitoso');
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   tags={"Auth"},
     *   summary="Iniciar sesión",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(required={"email","password"},
     *       @OA\Property(property="email",    type="string", format="email"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Token de acceso",
     *     @OA\JsonContent(
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="user",  ref="#/components/schemas/UserResource")
     *     )
     *   ),
     *   @OA\Response(response=401, description="Credenciales inválidas"),
     *   @OA\Response(response=429, description="Demasiados intentos")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->loginWithCredentials(
            $request->email,
            $request->password
        );

        return $this->successResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Sesión iniciada exitosamente');
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/logout",
     *   tags={"Auth"},
     *   summary="Cerrar sesión — revocar token",
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(response=200, description="Sesión cerrada")
     * )
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout(auth()->user());
        return $this->successResponse(null, 'Sesión cerrada exitosamente');
    }

    /**
     * @OA\Get(
     *   path="/api/v1/auth/me",
     *   tags={"Auth"},
     *   summary="Obtener perfil del usuario autenticado",
     *   security={{"BearerAuth":{}}},
     *   @OA\Response(response=200, description="Perfil del usuario")
     * )
     */
    public function me(): JsonResponse
    {
        return $this->successResponse(
            new UserResource(auth()->user()->load('vehicles')),
            'Perfil obtenido'
        );
    }
}
```

---

## 8. API Resources

### IncidentResource.php

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncidentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'status'           => $this->status,
            'status_label'     => $this->status->label(),
            'incident_date'    => $this->incident_date->format('Y-m-d'),
            'incident_time'    => $this->incident_time,
            'location_address' => $this->location_address,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'cover_photo_url'  => $this->cover_photo_url,
            'photos_count'     => $this->photos_count ?? $this->photos->count(),
            'vehicle'          => new VehicleResource($this->whenLoaded('vehicle')),
            'has_pdf'          => ! empty($this->report_pdf_url),
            'created_at'       => $this->created_at->toIso8601String(),
        ];
    }
}
```

### DocumentResource.php

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'document_number' => $this->document_number,
            'issue_date'      => $this->issue_date?->format('Y-m-d'),
            'expiry_date'     => $this->expiry_date->format('Y-m-d'),
            'status'          => $this->status,   // Computed accessor
            'days_remaining'  => now()->diffInDays($this->expiry_date, false),
            'has_pdf'         => ! empty($this->pdf_url),
            'updated_at'      => $this->updated_at->toIso8601String(),
        ];
    }
}
```

---

## 9. Policies

### IncidentPolicy.php

```php
<?php

namespace App\Policies;

use App\Models\{Incident, User};

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Incident $incident): bool
    {
        return $user->id === $incident->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Incident $incident): bool
    {
        return $user->id === $incident->user_id;
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $user->id === $incident->user_id;
    }
}
```

---

## 10. Comandos de Consola y Schedule

### SendDocumentExpiryAlerts.php

```php
<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\FcmNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDocumentExpiryAlerts extends Command
{
    protected $signature   = 'chocapp:document-expiry-alerts';
    protected $description = 'Enviar alertas push de documentos próximos a vencer';

    public function __construct(private readonly FcmNotificationService $fcm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $documents = Document::with('vehicle.user')
            ->expiringSoon(30)
            ->get();

        $count = 0;
        foreach ($documents as $doc) {
            $user = $doc->vehicle?->user;
            if (! $user) continue;

            $daysLeft = now()->diffInDays($doc->expiry_date);
            $typeLabel = match($doc->type) {
                'SOAT'          => 'SOAT',
                'TECNOMECANICA' => 'Tecnomecánica',
                'LICENCIA'      => 'Licencia de conducción',
                default         => $doc->type,
            };

            $this->fcm->send(
                $user,
                "⚠️ {$typeLabel} próximo a vencer",
                "Tu {$typeLabel} vence en {$daysLeft} días. ¡Renuévalo ya!",
                ['type' => 'DOCUMENT_EXPIRING', 'document_id' => $doc->id]
            );

            $count++;
        }

        Log::channel('daily')->info("ChocApp: Alertas de documentos enviadas: {$count}");
        $this->info("Alertas enviadas: {$count}");

        return Command::SUCCESS;
    }
}
```

### Kernel.php — Schedule

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Alertas de documentos venciendo — todos los días a las 8AM
        $schedule->command('chocapp:document-expiry-alerts')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Recordatorios de mantenimiento — todos los lunes a las 9AM
        $schedule->command('chocapp:maintenance-reminders')
                 ->weeklyOn(1, '09:00')
                 ->withoutOverlapping();

        // Limpiar tokens Sanctum expirados (90 días = 2160 horas)
        $schedule->command('sanctum:prune-expired --hours=2160')
                 ->daily();

        // Limpiar jobs fallidos con más de 7 días
        $schedule->command('queue:prune-failed --hours=168')
                 ->weekly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
```

---

## 11. Middleware

### ForceJsonResponse.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
```

### SecurityHeaders.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private array $headers = [
        'X-Content-Type-Options'    => 'nosniff',
        'X-Frame-Options'           => 'DENY',
        'X-XSS-Protection'          => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Content-Security-Policy'   => "default-src 'none'",
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=()',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($this->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
```

---

## 12. Rutas (api.php)

```php
<?php

use App\Http\Controllers\Api\{
    AuthController, VehicleController, DocumentController,
    IncidentController, MaintenanceController,
    NotificationController, SupportController,
};
use Illuminate\Support\Facades\Route;

// ── Rutas públicas ──────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    Route::post('auth/register',          [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('auth/login',             [AuthController::class, 'login'])->middleware('throttle:auth');
    Route::post('auth/social',            [AuthController::class, 'social'])->middleware('throttle:auth');
    Route::post('auth/password/forgot',   [AuthController::class, 'forgotPassword']);
    Route::post('auth/password/reset',    [AuthController::class, 'resetPassword']);
});

// ── Rutas protegidas ────────────────────────────────────────────────────────
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'force.json', 'security.headers'])
    ->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);
    Route::put('auth/profile', [AuthController::class, 'updateProfile']);

    // Vehículos
    Route::apiResource('vehicles', VehicleController::class);

    // Documentos — anidados bajo vehículo
    Route::prefix('vehicles/{vehicle}')->group(function () {
        Route::get('documents',                         [DocumentController::class,    'index']);
        Route::post('documents',                        [DocumentController::class,    'upsert']);
        Route::delete('documents/{document}',           [DocumentController::class,    'destroy']);

        // Mantenimiento — anidado bajo vehículo
        Route::get('maintenance',                       [MaintenanceController::class, 'index']);
        Route::post('maintenance',                      [MaintenanceController::class, 'store']);
        Route::put('maintenance/{record}',              [MaintenanceController::class, 'update']);
        Route::delete('maintenance/{record}',           [MaintenanceController::class, 'destroy']);
    });

    // Incidentes
    Route::apiResource('incidents', IncidentController::class);
    Route::get('incidents/{incident}/export-pdf',       [IncidentController::class, 'exportPdf'])
         ->middleware('throttle:heavy');
    Route::post('incidents/{incident}/photos',          [IncidentController::class, 'addPhoto'])
         ->middleware('throttle:heavy');
    Route::delete('incidents/{incident}/photos/{photo}',[IncidentController::class, 'removePhoto']);
    Route::post('incidents/{incident}/third-parties',   [IncidentController::class, 'addThirdParty']);

    // Notificaciones
    Route::get('notifications',                         [NotificationController::class, 'index']);
    Route::patch('notifications/read-all',              [NotificationController::class, 'markAllRead']);
    Route::patch('notifications/{id}/read',             [NotificationController::class, 'markRead']);
    Route::delete('notifications/{id}',                 [NotificationController::class, 'destroy']);

    // Soporte
    Route::get('support/emergency-contacts',            [SupportController::class, 'emergencyContacts']);
    Route::get('support/workshops',                     [SupportController::class, 'workshops']);
});
```

**Rate Limiters — AppServiceProvider.php:**

```php
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

RateLimiter::for('api',   fn($r) => Limit::perMinute(60)->by($r->user()?->id ?: $r->ip()));
RateLimiter::for('auth',  fn($r) => Limit::perMinute(5)->by($r->ip()));
RateLimiter::for('heavy', fn($r) => Limit::perMinute(10)->by($r->user()?->id));
```

---

## 13. Template PDF

### resources/views/pdf/incident-report.blade.php

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body        { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; }
        .header     { background: #E8302A; color: #fff; padding: 20px; text-align: center; }
        .header h1  { margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header p   { margin: 4px 0 0; font-size: 11px; opacity: 0.85; }
        .section    { margin: 16px 0; border-left: 4px solid #E8302A; padding-left: 12px; }
        .section h2 { font-size: 13px; margin: 0 0 8px; color: #E8302A; }
        .grid       { width: 100%; border-collapse: collapse; }
        .grid td    { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .grid td:first-child { font-weight: bold; width: 40%; color: #555; }
        .photos     { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .photo-box  { width: 160px; text-align: center; }
        .photo-box img  { width: 160px; height: 110px; object-fit: cover; border-radius: 4px; }
        .photo-box span { font-size: 9px; color: #666; display: block; margin-top: 2px; }
        .badge      { display: inline-block; padding: 2px 8px; border-radius: 10px;
                      font-size: 10px; font-weight: bold; }
        .badge-reportado  { background: #fef3c7; color: #92400e; }
        .badge-finalizado { background: #d1fae5; color: #065f46; }
        .footer     { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px;
                      text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

<div class="header">
    <h1>ChocApp — Reporte Oficial de Accidente</h1>
    <p>Generado el {{ $generatedAt }} | ID: {{ $incident->id }}</p>
</div>

<div class="section">
    <h2>Información del Incidente</h2>
    <table class="grid">
        <tr><td>Estado</td><td>
            <span class="badge badge-{{ strtolower($incident->status->value) }}">
                {{ $incident->status->label() }}
            </span>
        </td></tr>
        <tr><td>Título</td><td>{{ $incident->title }}</td></tr>
        <tr><td>Fecha</td><td>{{ $incident->incident_date->format('d/m/Y') }}</td></tr>
        <tr><td>Hora</td><td>{{ $incident->incident_time }}</td></tr>
        <tr><td>Dirección</td><td>{{ $incident->location_address }}</td></tr>
        <tr><td>Coordenadas</td><td>{{ $incident->latitude }}, {{ $incident->longitude }}</td></tr>
        <tr><td>Clima</td><td>{{ $incident->weather_condition }}</td></tr>
        <tr><td>Estado vía</td><td>{{ $incident->road_condition }}</td></tr>
        @if($incident->police_report_number)
        <tr><td>N° denuncia policial</td><td>{{ $incident->police_report_number }}</td></tr>
        @endif
    </table>
    <p style="margin-top:8px">{{ $incident->description }}</p>
</div>

<div class="section">
    <h2>Vehículo del Reportante</h2>
    <table class="grid">
        <tr><td>Placa</td><td>{{ $incident->vehicle->plate }}</td></tr>
        <tr><td>Marca / Modelo</td><td>{{ $incident->vehicle->brand }} {{ $incident->vehicle->model }}</td></tr>
        <tr><td>Año / Color</td><td>{{ $incident->vehicle->year }} / {{ $incident->vehicle->color }}</td></tr>
    </table>
</div>

<div class="section">
    <h2>Conductora / Propietario</h2>
    <table class="grid">
        <tr><td>Nombre</td><td>{{ $incident->user->name }}</td></tr>
        <tr><td>Documento</td><td>{{ $incident->user->id_type }}: {{ $incident->user->id_number }}</td></tr>
        <tr><td>Teléfono</td><td>{{ $incident->user->phone_number }}</td></tr>
    </table>
</div>

@if($incident->thirdParties->count() > 0)
<div class="section">
    <h2>Terceros Implicados</h2>
    @foreach($incident->thirdParties as $party)
    <table class="grid" style="margin-bottom:8px">
        <tr><td>Tipo</td><td>{{ $party->party_type }}</td></tr>
        @if($party->plate)<tr><td>Placa</td><td>{{ $party->plate }}</td></tr>@endif
        @if($party->driver_name)<tr><td>Conductor</td><td>{{ $party->driver_name }}</td></tr>@endif
        @if($party->insurance_company)<tr><td>Aseguradora</td><td>{{ $party->insurance_company }} — {{ $party->insurance_policy }}</td></tr>@endif
    </table>
    @endforeach
</div>
@endif

@if($incident->photos->count() > 0)
<div class="section">
    <h2>Evidencia Fotográfica ({{ $incident->photos->count() }} fotos)</h2>
    <div class="photos">
        @foreach($incident->photos as $photo)
        <div class="photo-box">
            <img src="{{ $photo->image_url }}" alt="{{ $photo->angle }}">
            <span>{{ $photo->angle }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="footer">
    <p>Este reporte fue generado automáticamente por ChocApp y puede ser utilizado como evidencia legal.</p>
    <p>ChocApp — Tu aliado en el camino | {{ $generatedAt }}</p>
</div>

</body>
</html>
```

---

## 14. Docker Compose

```yaml
# docker-compose.yml — ChocApp
services:

  nginx:
    image: nginx:1.25-alpine
    container_name: ${CONTAINER_PREFIX}_nginx
    volumes:
      - ./docker-files/nginx/site.conf:/etc/nginx/conf.d/default.conf:ro
      - .:/var/www/html
      - /var/www/chocapp/multimedia:/var/www/html/multimedia
    ports:
      - "${EXPOSED_PORT}:80"
    restart: unless-stopped
    networks:
      - chocapp
    depends_on:
      app:
        condition: service_healthy

  app:
    container_name: ${CONTAINER_PREFIX}_app
    build:
      context: .
      dockerfile: Dockerfile
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
      - ./docker-files/php/php.ini:/usr/local/etc/php/php.ini:ro
      - /var/www/chocapp/multimedia:/var/www/html/multimedia
    networks:
      - chocapp
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_healthy
    healthcheck:
      test: ["CMD", "php", "-v"]
      interval: 10s
      timeout: 5s
      retries: 5

  db:
    image: mysql:8.0
    container_name: ${CONTAINER_PREFIX}_db
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE:      ${DB_DATABASE}
      MYSQL_USER:          ${DB_USERNAME}
      MYSQL_PASSWORD:      ${DB_PASSWORD}
    volumes:
      - chocapp_db_data:/var/lib/mysql
      - ./docker-files/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    networks:
      - chocapp
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost",
             "-u", "root", "-p${DB_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 10

  redis:
    image: redis:7-alpine
    container_name: ${CONTAINER_PREFIX}_redis
    restart: unless-stopped
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - chocapp_redis_data:/data
    networks:
      - chocapp
    healthcheck:
      test: ["CMD", "redis-cli", "-a", "${REDIS_PASSWORD}", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  scheduler:
    container_name: ${CONTAINER_PREFIX}_scheduler
    build:
      context: .
      dockerfile: Dockerfile
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
    networks:
      - chocapp
    command: >
      sh -c "while true; do
        php artisan schedule:run --verbose --no-interaction;
        sleep 60;
      done"
    depends_on:
      app:
        condition: service_healthy

  queue:
    container_name: ${CONTAINER_PREFIX}_queue
    build:
      context: .
      dockerfile: Dockerfile
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - .:/var/www/html
    networks:
      - chocapp
    command: >
      php artisan queue:work redis
        --sleep=3
        --tries=3
        --max-time=3600
        --queue=notifications,pdf,default
    depends_on:
      app:
        condition: service_healthy

networks:
  chocapp:
    driver: bridge

volumes:
  chocapp_db_data:
  chocapp_redis_data:
```

---

## 15. Dockerfile

```dockerfile
FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    bash curl git unzip \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libzip-dev oniguruma-dev icu-dev \
    wkhtmltopdf ttf-freefont

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo_mysql mbstring zip exif gd intl pcntl bcmath opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Non-root user
RUN addgroup -g 1000 www \
 && adduser -u 1000 -G www -s /bin/sh -D www

USER www

EXPOSE 9000
CMD ["php-fpm"]
```

---

## 16. Nginx Config

### docker-files/nginx/site.conf

```nginx
server {
    listen 80;
    server_name _;

    root /var/www/html/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options         "DENY"           always;
    add_header X-Content-Type-Options  "nosniff"        always;
    add_header X-XSS-Protection        "1; mode=block"  always;
    add_header Referrer-Policy         "strict-origin-when-cross-origin" always;

    # API — route to Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass   app:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 300;
    }

    # Static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Block hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Swagger UI — only in non-prod
    location /api/documentation {
        try_files $uri $uri/ /index.php?$query_string;
    }

    client_max_body_size 50M;

    access_log  /var/log/nginx/chocapp_access.log;
    error_log   /var/log/nginx/chocapp_error.log;
}
```

---

## 17. Jenkinsfile

```groovy
pipeline {
    agent any
    options {
        disableConcurrentBuilds()
        timeout(time: 30, unit: 'MINUTES')
    }

    parameters {
        booleanParam(
            name: 'DEPLOY_TO_PRODUCTION',
            defaultValue: false,
            description: '¡CUIDADO! Marcar para desplegar a producción ¡CUIDADO!'
        )
    }

    environment {
        CHOCAPP_CREDS = credentials('chocapp')

        // Staging
        STG_CONTAINER_PREFIX = 'chocapp_staging'
        STG_EXPOSED_PORT     = '9092'
        STG_DOMAIN           = 'stg.chocapp.com'
        STG_VHOST_CONF       = 'stg.chocapp.com.conf'
        STG_DIR_DESTINY      = '/var/www/chocapp/staging/stg.chocapp.com'

        // Production
        PROD_CONTAINER_PREFIX = 'chocapp_production'
        PROD_EXPOSED_PORT     = '9091'
        PROD_DOMAIN           = 'api.chocapp.com'
        PROD_VHOST_CONF       = 'api.chocapp.com.conf'
        PROD_DIR_DESTINY      = '/var/www/chocapp/production/api.chocapp.com'

        NGINX_SITES_AVAILABLE = '/etc/nginx/sites-available'
        NGINX_SITES_ENABLED   = '/etc/nginx/sites-enabled'
    }

    stages {

        // ── 0. Cargar credenciales ───────────────────────────────────────────
        stage('Load credentials') {
            steps {
                script {
                    echo "Branch: ${env.BRANCH_NAME} | Workspace: ${env.WORKSPACE}"
                    def raw = sh(
                        script: "grep -v '^[[:space:]]*#' \"${CHOCAPP_CREDS}\" | grep -v '^[[:space:]]*\$'",
                        returnStdout: true
                    ).trim()
                    raw.split('\n').each { line ->
                        def idx = line.indexOf('=')
                        if (idx > 0) {
                            env."${line.substring(0, idx).trim()}" = line.substring(idx + 1).trim()
                        }
                    }
                    echo "Credenciales cargadas correctamente."
                }
            }
        }

        // ── 1. Security scan ─────────────────────────────────────────────────
        stage('Security scan') {
            steps {
                script {
                    sh 'php -l public/index.php || true'
                    sh 'composer audit --no-interaction || true'
                }
            }
        }

        // ── 2. ENV files ─────────────────────────────────────────────────────
        stage('ENV - staging') {
            when { branch 'staging' }
            steps {
                sh 'cp .stg .env'
                script {
                    def props = [
                        'APP_KEY'          : env.STG_APP_KEY,
                        'DB_CONNECTION'    : env.STG_DB_CONNECTION,
                        'DB_HOST'          : env.STG_DB_HOST,
                        'DB_PORT'          : env.STG_DB_PORT,
                        'DB_DATABASE'      : env.STG_DB_DATABASE,
                        'DB_USERNAME'      : env.STG_DB_USERNAME,
                        'DB_PASSWORD'      : env.STG_DB_PASSWORD,
                        'DB_ROOT_PASSWORD' : env.STG_DB_ROOT_PASSWORD,
                        'REDIS_PASSWORD'   : env.STG_REDIS_PASSWORD,
                        'AWS_ACCESS_KEY_ID'    : env.AWS_ACCESS_KEY_ID,
                        'AWS_SECRET_ACCESS_KEY': env.AWS_SECRET_ACCESS_KEY,
                        'AWS_DEFAULT_REGION'   : env.AWS_DEFAULT_REGION,
                        'AWS_BUCKET'           : env.STG_AWS_BUCKET,
                        'FCM_PROJECT_ID'   : env.FCM_PROJECT_ID,
                        'FCM_SERVER_KEY'   : env.FCM_SERVER_KEY,
                        'MAIL_HOST'        : env.MAIL_HOST,
                        'MAIL_PORT'        : env.MAIL_PORT,
                        'MAIL_USERNAME'    : env.MAIL_USERNAME,
                        'MAIL_PASSWORD'    : env.MAIL_PASSWORD,
                        'MAIL_ENCRYPTION'  : env.MAIL_ENCRYPTION,
                        'MAIL_FROM_ADDRESS': env.MAIL_FROM_ADDRESS,
                    ]
                    def content = readFile('.env')
                    props.each { k, v ->
                        content = content.replaceAll(/(^|\n)${k}=.*/, "\$1${k}=${v}")
                    }
                    writeFile(file: '.env', text: content)
                }
            }
        }

        stage('ENV - production') {
            when { branch 'master' }
            steps {
                sh 'cp .prod .env'
                script {
                    def props = [
                        'APP_KEY'          : env.PROD_APP_KEY,
                        'DB_CONNECTION'    : env.PROD_DB_CONNECTION,
                        'DB_HOST'          : env.PROD_DB_HOST,
                        'DB_PORT'          : env.PROD_DB_PORT,
                        'DB_DATABASE'      : env.PROD_DB_DATABASE,
                        'DB_USERNAME'      : env.PROD_DB_USERNAME,
                        'DB_PASSWORD'      : env.PROD_DB_PASSWORD,
                        'DB_ROOT_PASSWORD' : env.PROD_DB_ROOT_PASSWORD,
                        'REDIS_PASSWORD'   : env.PROD_REDIS_PASSWORD,
                        'AWS_ACCESS_KEY_ID'    : env.AWS_ACCESS_KEY_ID,
                        'AWS_SECRET_ACCESS_KEY': env.AWS_SECRET_ACCESS_KEY,
                        'AWS_DEFAULT_REGION'   : env.AWS_DEFAULT_REGION,
                        'AWS_BUCKET'           : env.PROD_AWS_BUCKET,
                        'FCM_PROJECT_ID'   : env.FCM_PROJECT_ID,
                        'FCM_SERVER_KEY'   : env.FCM_SERVER_KEY,
                        'MAIL_HOST'        : env.MAIL_HOST,
                        'MAIL_PORT'        : env.MAIL_PORT,
                        'MAIL_USERNAME'    : env.MAIL_USERNAME,
                        'MAIL_PASSWORD'    : env.MAIL_PASSWORD,
                        'MAIL_ENCRYPTION'  : env.MAIL_ENCRYPTION,
                        'MAIL_FROM_ADDRESS': env.MAIL_FROM_ADDRESS,
                    ]
                    def content = readFile('.env')
                    props.each { k, v ->
                        content = content.replaceAll(/(^|\n)${k}=.*/, "\$1${k}=${v}")
                    }
                    writeFile(file: '.env', text: content)
                }
            }
        }

        // ── 3. Port validation ───────────────────────────────────────────────
        stage('Port validation - staging') {
            when { branch 'staging' }
            steps {
                script {
                    def port   = env.STG_EXPOSED_PORT
                    def prefix = env.STG_CONTAINER_PREFIX
                    def inUse  = sh(script: "ss -tuln | grep -c ':${port} ' || true",
                                    returnStdout: true).trim().toInteger()
                    if (inUse > 0) {
                        def own = sh(script: "docker ps --filter 'publish=${port}' --filter 'name=${prefix}' -q | wc -l",
                                     returnStdout: true).trim().toInteger()
                        if (own == 0) {
                            error("Puerto ${port} ocupado por otro proceso.")
                        }
                        echo "Puerto ${port} en uso por contenedores propios — serán reemplazados."
                    } else {
                        echo "Puerto ${port} disponible."
                    }
                }
            }
        }

        stage('Port validation - production') {
            when { allOf { branch 'master'; expression { params.DEPLOY_TO_PRODUCTION } } }
            steps {
                script {
                    def port   = env.PROD_EXPOSED_PORT
                    def prefix = env.PROD_CONTAINER_PREFIX
                    def inUse  = sh(script: "ss -tuln | grep -c ':${port} ' || true",
                                    returnStdout: true).trim().toInteger()
                    if (inUse > 0) {
                        def own = sh(script: "docker ps --filter 'publish=${port}' --filter 'name=${prefix}' -q | wc -l",
                                     returnStdout: true).trim().toInteger()
                        if (own == 0) { error("Puerto ${port} ocupado por otro proceso.") }
                        echo "Puerto ${port} en uso por contenedores propios — serán reemplazados."
                    } else {
                        echo "Puerto ${port} disponible."
                    }
                }
            }
        }

        // ── 4. Virtualhost setup ─────────────────────────────────────────────
        stage('Virtualhost setup - staging') {
            when { branch 'staging' }
            steps {
                script {
                    def vhostFile    = "${env.NGINX_SITES_AVAILABLE}/${env.STG_VHOST_CONF}"
                    def vhostEnabled = "${env.NGINX_SITES_ENABLED}/${env.STG_VHOST_CONF}"
                    if (sh(script: "[ -f '${vhostFile}' ] && echo yes || echo no",
                           returnStdout: true).trim() == 'no') {
                        def cfg = """server {
    listen 80;
    server_name ${env.STG_DOMAIN};
    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    location / {
        proxy_pass         http://127.0.0.1:${env.STG_EXPOSED_PORT};
        proxy_http_version 1.1;
        proxy_set_header   Host              \$host;
        proxy_set_header   X-Real-IP         \$remote_addr;
        proxy_set_header   X-Forwarded-For   \$proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto \$scheme;
        proxy_connect_timeout 300;
        proxy_send_timeout    300;
        proxy_read_timeout    300;
    }
    location ~ /\\.(?!well-known).* { deny all; }
}"""
                        writeFile(file: '/tmp/stg_chocapp_vhost.conf', text: cfg)
                        sh "sudo cp /tmp/stg_chocapp_vhost.conf ${vhostFile}"
                        sh "sudo ln -sf ${vhostFile} ${vhostEnabled}"
                        sh "sudo nginx -t && sudo systemctl reload nginx"
                        echo "Virtualhost ${env.STG_DOMAIN} creado."
                    } else {
                        echo "Virtualhost ya existe: ${vhostFile}"
                    }
                }
            }
        }

        stage('Virtualhost setup - production') {
            when { allOf { branch 'master'; expression { params.DEPLOY_TO_PRODUCTION } } }
            steps {
                script {
                    def vhostFile    = "${env.NGINX_SITES_AVAILABLE}/${env.PROD_VHOST_CONF}"
                    def vhostEnabled = "${env.NGINX_SITES_ENABLED}/${env.PROD_VHOST_CONF}"
                    if (sh(script: "[ -f '${vhostFile}' ] && echo yes || echo no",
                           returnStdout: true).trim() == 'no') {
                        def cfg = """server {
    listen 80;
    server_name ${env.PROD_DOMAIN};
    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    location / {
        proxy_pass         http://127.0.0.1:${env.PROD_EXPOSED_PORT};
        proxy_http_version 1.1;
        proxy_set_header   Host              \$host;
        proxy_set_header   X-Real-IP         \$remote_addr;
        proxy_set_header   X-Forwarded-For   \$proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto \$scheme;
        proxy_connect_timeout 300;
        proxy_send_timeout    300;
        proxy_read_timeout    300;
    }
    location ~ /\\.(?!well-known).* { deny all; }
}"""
                        writeFile(file: '/tmp/prod_chocapp_vhost.conf', text: cfg)
                        sh "sudo cp /tmp/prod_chocapp_vhost.conf ${vhostFile}"
                        sh "sudo ln -sf ${vhostFile} ${vhostEnabled}"
                        sh "sudo nginx -t && sudo systemctl reload nginx"
                        echo "Virtualhost ${env.PROD_DOMAIN} creado."
                    } else {
                        echo "Virtualhost ya existe: ${vhostFile}"
                    }
                }
            }
        }

        // ── 5. Deploy staging ────────────────────────────────────────────────
        stage('Deploy - staging') {
            when { branch 'staging' }
            steps {
                echo 'Desplegando ChocApp en staging...'
                script {
                    sh "mkdir -p ${env.STG_DIR_DESTINY}"
                    sh "cd ${env.STG_DIR_DESTINY} && docker compose --project-name ${env.STG_CONTAINER_PREFIX} down --remove-orphans || true"
                    sh "docker rm -f ${env.STG_CONTAINER_PREFIX}_app ${env.STG_CONTAINER_PREFIX}_nginx ${env.STG_CONTAINER_PREFIX}_db ${env.STG_CONTAINER_PREFIX}_redis ${env.STG_CONTAINER_PREFIX}_queue ${env.STG_CONTAINER_PREFIX}_scheduler 2>/dev/null || true"

                    sh """
                        rsync -aO --no-owner --no-group --delete \\
                            --exclude='.git' \\
                            --exclude='vendor' \\
                            --exclude='storage/' \\
                            --exclude='bootstrap/cache/' \\
                            --exclude='node_modules' \\
                            --exclude='multimedia/' \\
                            . ${env.STG_DIR_DESTINY}/
                    """

                    sh "printf 'CONTAINER_PREFIX=${env.STG_CONTAINER_PREFIX}\\nEXPOSED_PORT=${env.STG_EXPOSED_PORT}\\n' > ${env.STG_DIR_DESTINY}/.env"

                    sh """
                        mkdir -p \\
                            ${env.STG_DIR_DESTINY}/storage/logs \\
                            ${env.STG_DIR_DESTINY}/storage/app/public \\
                            ${env.STG_DIR_DESTINY}/storage/framework/cache/data \\
                            ${env.STG_DIR_DESTINY}/storage/framework/sessions \\
                            ${env.STG_DIR_DESTINY}/storage/framework/views \\
                            ${env.STG_DIR_DESTINY}/bootstrap/cache
                    """

                    sh "cd ${env.STG_DIR_DESTINY} && docker compose --project-name ${env.STG_CONTAINER_PREFIX} up --build -d"
                    sh "timeout 120 sh -c 'until docker exec ${env.STG_CONTAINER_PREFIX}_app php -v > /dev/null 2>&1; do sleep 3; done'"

                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'rm -f /var/www/html/bootstrap/cache/*.php'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan key:generate --force'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan migrate:fresh --seed --force'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan optimize:clear'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan optimize'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan l5-swagger:generate'"
                    sh "docker exec ${env.STG_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true"

                    echo "Staging desplegado en http://${env.STG_DOMAIN} (puerto ${env.STG_EXPOSED_PORT})"
                    echo "Swagger UI disponible en http://${env.STG_DOMAIN}/api/documentation"
                }
            }
        }

        // ── 6. Deploy production ─────────────────────────────────────────────
        stage('Deploy - production') {
            when { allOf { branch 'master'; expression { params.DEPLOY_TO_PRODUCTION } } }
            steps {
                echo 'Desplegando ChocApp en producción...'
                script {
                    sh "mkdir -p ${env.PROD_DIR_DESTINY}"
                    sh "cd ${env.PROD_DIR_DESTINY} && docker compose --project-name ${env.PROD_CONTAINER_PREFIX} down --remove-orphans || true"
                    sh "docker rm -f ${env.PROD_CONTAINER_PREFIX}_app ${env.PROD_CONTAINER_PREFIX}_nginx ${env.PROD_CONTAINER_PREFIX}_db ${env.PROD_CONTAINER_PREFIX}_redis ${env.PROD_CONTAINER_PREFIX}_queue ${env.PROD_CONTAINER_PREFIX}_scheduler 2>/dev/null || true"

                    sh """
                        rsync -aO --no-owner --no-group --delete \\
                            --exclude='.git' \\
                            --exclude='vendor' \\
                            --exclude='storage/' \\
                            --exclude='bootstrap/cache/' \\
                            --exclude='node_modules' \\
                            --exclude='multimedia/' \\
                            . ${env.PROD_DIR_DESTINY}/
                    """

                    sh "printf 'CONTAINER_PREFIX=${env.PROD_CONTAINER_PREFIX}\\nEXPOSED_PORT=${env.PROD_EXPOSED_PORT}\\n' > ${env.PROD_DIR_DESTINY}/.env"

                    sh """
                        mkdir -p \\
                            ${env.PROD_DIR_DESTINY}/storage/logs \\
                            ${env.PROD_DIR_DESTINY}/storage/app/public \\
                            ${env.PROD_DIR_DESTINY}/storage/framework/cache/data \\
                            ${env.PROD_DIR_DESTINY}/storage/framework/sessions \\
                            ${env.PROD_DIR_DESTINY}/storage/framework/views \\
                            ${env.PROD_DIR_DESTINY}/bootstrap/cache
                    """

                    sh "cd ${env.PROD_DIR_DESTINY} && docker compose --project-name ${env.PROD_CONTAINER_PREFIX} up --build -d"
                    sh "timeout 120 sh -c 'until docker exec ${env.PROD_CONTAINER_PREFIX}_app php -v > /dev/null 2>&1; do sleep 3; done'"

                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'rm -f /var/www/html/bootstrap/cache/*.php'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app composer install --working-dir=/var/www/html --no-interaction --prefer-dist --optimize-autoloader --no-dev"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan key:generate --force'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan migrate --force'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan config:cache && php artisan route:cache && php artisan view:cache'"
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app sh -c 'cd /var/www/html && php artisan optimize'"
                    // Swagger NO se genera en producción
                    sh "docker exec ${env.PROD_CONTAINER_PREFIX}_app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true"

                    echo "Producción desplegada en https://${env.PROD_DOMAIN} (puerto ${env.PROD_EXPOSED_PORT})"
                }
            }
        }
    }

    post {
        always {
            script {
                try { deleteDir() } catch (Exception e) { echo "Cleanup warning: ${e.message}" }
            }
        }
        success {
            echo "✅ Pipeline ChocApp finalizado con éxito — Rama: ${env.BRANCH_NAME}"
        }
        failure {
            echo "❌ Pipeline ChocApp FALLIDO — Rama: ${env.BRANCH_NAME}. Revisa los logs."
        }
    }
}
```

---

## 18. MCP — Model Context Protocol

### mcp/chocapp-api.json

```json
{
  "schema_version": "1.0",
  "name": "chocapp-api",
  "description": "ChocApp accident documentation API for Colombian motorcycle and car drivers",
  "base_url": "https://api.chocapp.com/api/v1",
  "authentication": {
    "type": "bearer",
    "header": "Authorization",
    "format": "Bearer {token}",
    "obtain_via": "POST /auth/login",
    "token_ttl_days": 90
  },
  "tools": [
    {
      "name": "register_user",
      "description": "Register a new ChocApp user",
      "method": "POST",
      "path": "/auth/register",
      "content_type": "multipart/form-data",
      "auth_required": false,
      "parameters": {
        "required": ["name","email","password","password_confirmation",
                     "id_type","id_number","phone_number","terms_accepted"],
        "optional": ["profile_pic"],
        "schema": {
          "name":                 { "type": "string", "maxLength": 100 },
          "email":                { "type": "string", "format": "email" },
          "password":             { "type": "string", "minLength": 8 },
          "password_confirmation":{ "type": "string" },
          "id_type":              { "type": "string", "enum": ["CC","CE","PPT","PASAPORTE"] },
          "id_number":            { "type": "string" },
          "phone_number":         { "type": "string" },
          "terms_accepted":       { "type": "boolean" },
          "profile_pic":          { "type": "file", "mimes": "jpeg,png,webp", "maxKB": 5120 }
        }
      },
      "returns": { "user": "UserResource", "token": "string" }
    },
    {
      "name": "login",
      "description": "Authenticate and obtain a Bearer token. Rate limited: 5/min per IP.",
      "method": "POST",
      "path": "/auth/login",
      "content_type": "application/json",
      "auth_required": false,
      "parameters": {
        "required": ["email","password"],
        "schema": {
          "email":    { "type": "string", "format": "email" },
          "password": { "type": "string" }
        }
      },
      "returns": { "user": "UserResource", "token": "string" }
    },
    {
      "name": "get_me",
      "description": "Get the authenticated user profile with vehicles",
      "method": "GET",
      "path": "/auth/me",
      "auth_required": true,
      "returns": { "user": "UserResource" }
    },
    {
      "name": "logout",
      "description": "Revoke current Bearer token",
      "method": "POST",
      "path": "/auth/logout",
      "auth_required": true
    },
    {
      "name": "list_vehicles",
      "description": "List all vehicles registered for the authenticated user",
      "method": "GET",
      "path": "/vehicles",
      "auth_required": true,
      "returns": { "vehicles": "VehicleResource[]" }
    },
    {
      "name": "get_vehicle_documents",
      "description": "Get SOAT, Tecnomecánica and Licencia status for a vehicle. Status field is computed: VIGENTE | VENCE_PRONTO | VENCIDO",
      "method": "GET",
      "path": "/vehicles/{vehicle_id}/documents",
      "auth_required": true,
      "path_params": {
        "vehicle_id": { "type": "string", "format": "uuid" }
      },
      "returns": { "documents": "DocumentResource[]" }
    },
    {
      "name": "get_vehicle_maintenance",
      "description": "Get maintenance history for a vehicle",
      "method": "GET",
      "path": "/vehicles/{vehicle_id}/maintenance",
      "auth_required": true,
      "path_params": {
        "vehicle_id": { "type": "string", "format": "uuid" }
      },
      "returns": { "records": "MaintenanceResource[]" }
    },
    {
      "name": "list_incidents",
      "description": "List all accidents for the authenticated user, paginated",
      "method": "GET",
      "path": "/incidents",
      "auth_required": true,
      "query_params": {
        "status":   { "type": "string", "enum": ["BORRADOR","REPORTADO","EN_REVISION","FINALIZADO"] },
        "from":     { "type": "string", "format": "date" },
        "to":       { "type": "string", "format": "date" },
        "per_page": { "type": "integer", "default": 15 },
        "page":     { "type": "integer", "default": 1 }
      },
      "returns": { "incidents": "IncidentResource[]", "meta": "PaginationMeta" }
    },
    {
      "name": "create_incident",
      "description": "Register a new vehicle accident with photo evidence. Minimum 4 angle photos required. Rate limited: 10/min.",
      "method": "POST",
      "path": "/incidents",
      "content_type": "multipart/form-data",
      "auth_required": true,
      "parameters": {
        "required": ["vehicle_id","description","incident_date","incident_time",
                     "latitude","longitude","weather_condition","road_condition","photos"],
        "optional": ["location_address","police_report_number"],
        "schema": {
          "vehicle_id":          { "type": "string", "format": "uuid" },
          "description":         { "type": "string", "minLength": 10, "maxLength": 2000 },
          "incident_date":       { "type": "string", "format": "date" },
          "incident_time":       { "type": "string", "format": "HH:mm", "example": "14:30" },
          "location_address":    { "type": "string", "maxLength": 500 },
          "latitude":            { "type": "number", "minimum": -90, "maximum": 90 },
          "longitude":           { "type": "number", "minimum": -180, "maximum": 180 },
          "weather_condition":   { "type": "string", "enum": ["SOLEADO","LLUVIOSO","NUBLADO","NOCHE"] },
          "road_condition":      { "type": "string", "enum": ["BUEN_ESTADO","HUMEDO","HUECOS","DERRUMBE"] },
          "police_report_number":{ "type": "string" },
          "photos": {
            "type": "array",
            "minItems": 4,
            "description": "Array of photo objects. Each must have a file and an angle.",
            "items": {
              "file":  { "type": "file", "mimes": "jpeg,png,webp", "maxKB": 10240 },
              "angle": { "type": "string", "enum": ["FRONT","FRONT_RIGHT","RIGHT","REAR_RIGHT",
                         "REAR","REAR_LEFT","LEFT","FRONT_LEFT","INTERIOR","ODOMETER","EXTRA"] }
            }
          }
        }
      },
      "returns": { "incident": "IncidentDetailResource" }
    },
    {
      "name": "get_incident",
      "description": "Get full detail of an incident including photos and third parties",
      "method": "GET",
      "path": "/incidents/{incident_id}",
      "auth_required": true,
      "path_params": { "incident_id": { "type": "string", "format": "uuid" } },
      "returns": { "incident": "IncidentDetailResource" }
    },
    {
      "name": "export_incident_pdf",
      "description": "Generate and download the official incident PDF report. Returns binary PDF. Rate limited: 10/min.",
      "method": "GET",
      "path": "/incidents/{incident_id}/export-pdf",
      "auth_required": true,
      "path_params": { "incident_id": { "type": "string", "format": "uuid" } },
      "returns": { "content_type": "application/pdf", "disposition": "attachment" }
    },
    {
      "name": "get_notifications",
      "description": "Get notifications for the authenticated user",
      "method": "GET",
      "path": "/notifications",
      "auth_required": true,
      "query_params": {
        "unread_only": { "type": "boolean", "default": false },
        "per_page":    { "type": "integer", "default": 20 }
      }
    },
    {
      "name": "mark_notification_read",
      "description": "Mark a notification as read",
      "method": "PATCH",
      "path": "/notifications/{id}/read",
      "auth_required": true,
      "path_params": { "id": { "type": "string", "format": "uuid" } }
    },
    {
      "name": "get_emergency_contacts",
      "description": "Get list of Colombian emergency numbers: 123, tow trucks, lawyers, insurance companies",
      "method": "GET",
      "path": "/support/emergency-contacts",
      "auth_required": true
    },
    {
      "name": "get_workshops",
      "description": "Get list of geolocated workshops near a coordinate",
      "method": "GET",
      "path": "/support/workshops",
      "auth_required": true,
      "query_params": {
        "latitude":  { "type": "number" },
        "longitude": { "type": "number" },
        "radius_km": { "type": "integer", "default": 10 }
      }
    }
  ],
  "schemas": {
    "UserResource": {
      "id":              "string (uuid)",
      "name":            "string",
      "email":           "string",
      "id_type":         "string",
      "id_number":       "string",
      "phone_number":    "string",
      "profile_pic_url": "string|null",
      "vehicles_count":  "integer"
    },
    "VehicleResource": {
      "id":       "string (uuid)",
      "plate":    "string",
      "brand":    "string",
      "model":    "string",
      "year":     "integer",
      "color":    "string",
      "type":     "MOTOCICLETA|AUTOMOVIL"
    },
    "DocumentResource": {
      "id":              "string (uuid)",
      "type":            "SOAT|TECNOMECANICA|LICENCIA",
      "document_number": "string",
      "expiry_date":     "string (Y-m-d)",
      "status":          "VIGENTE|VENCE_PRONTO|VENCIDO",
      "days_remaining":  "integer"
    },
    "IncidentResource": {
      "id":               "string (uuid)",
      "title":            "string",
      "status":           "BORRADOR|REPORTADO|EN_REVISION|FINALIZADO",
      "incident_date":    "string (Y-m-d)",
      "location_address": "string",
      "cover_photo_url":  "string|null",
      "photos_count":     "integer",
      "has_pdf":          "boolean"
    }
  },
  "error_codes": {
    "401": "Unauthenticated — include valid Bearer token",
    "403": "Forbidden — you do not own this resource",
    "404": "Resource not found",
    "422": "Validation failed — check errors field for details",
    "429": "Too many requests — wait before retrying"
  },
  "rate_limits": {
    "general": "60 requests/minute per authenticated user",
    "auth":    "5 requests/minute per IP (login, register)",
    "heavy":   "10 requests/minute (PDF export, photo upload)"
  },
  "standard_response_format": {
    "success": {
      "success": true,
      "message": "string",
      "data":    "object|array|null",
      "meta":    "{ current_page, last_page, per_page, total } | null"
    },
    "error": {
      "success": false,
      "message": "string",
      "errors":  "object|null",
      "code":    "integer"
    }
  }
}
```

---

## 19. Tests

### tests/Feature/IncidentTest.php

```php
<?php

namespace Tests\Feature;

use App\Models\{User, Vehicle, Incident};
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
        $angles = ['FRONT','REAR','LEFT','RIGHT'];
        foreach ($angles as $angle) {
            $photos[] = [
                'file'  => UploadedFile::fake()->image("{$angle}.jpg", 800, 600),
                'angle' => $angle,
            ];
        }

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/incidents', [
                'vehicle_id'       => $this->vehicle->id,
                'description'      => 'Colisión en la Calle 72 con Carrera 11, Bogotá.',
                'incident_date'    => today()->toDateString(),
                'incident_time'    => '14:30',
                'location_address' => 'Calle 72 # 11-10, Bogotá',
                'latitude'         => 4.6800,
                'longitude'        => -74.0560,
                'weather_condition'=> 'LLUVIOSO',
                'road_condition'   => 'HUMEDO',
                'photos'           => $photos,
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'data' => ['id', 'status', 'cover_photo_url', 'photos_count']
                 ]);

        $this->assertDatabaseHas('incidents', [
            'user_id'    => $this->user->id,
            'vehicle_id' => $this->vehicle->id,
        ]);

        $this->assertDatabaseCount('incident_photos', 4);
        Storage::disk('s3')->assertCount('incidents', 4);
    }

    /** @test */
    public function incident_requires_minimum_4_photos(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/v1/incidents', [
                'vehicle_id'       => $this->vehicle->id,
                'description'      => 'Descripción del accidente con suficiente texto.',
                'incident_date'    => today()->toDateString(),
                'incident_time'    => '10:00',
                'latitude'         => 4.6800,
                'longitude'        => -74.0560,
                'weather_condition'=> 'SOLEADO',
                'road_condition'   => 'BUEN_ESTADO',
                'photos'           => [
                    ['file' => UploadedFile::fake()->image('f.jpg'), 'angle' => 'FRONT'],
                    ['file' => UploadedFile::fake()->image('r.jpg'), 'angle' => 'REAR'],
                    // Only 2 photos — should fail
                ],
            ]);

        $response->assertStatus(422)
                 ->assertJsonPath('success', false)
                 ->assertJsonValidationErrors(['photos']);
    }

    /** @test */
    public function user_cannot_view_another_users_incident(): void
    {
        $otherUser     = User::factory()->create();
        $otherVehicle  = Vehicle::factory()->create(['user_id' => $otherUser->id]);
        $otherIncident = Incident::factory()->create([
            'user_id'    => $otherUser->id,
            'vehicle_id' => $otherVehicle->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/incidents/{$otherIncident->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function document_status_accessor_returns_correct_values(): void
    {
        $vehicle  = $this->vehicle;
        $expired  = \App\Models\Document::factory()->create([
            'vehicle_id'  => $vehicle->id,
            'type'        => 'SOAT',
            'expiry_date' => now()->subDay()->toDateString(),
        ]);
        $soon = \App\Models\Document::factory()->create([
            'vehicle_id'  => $vehicle->id,
            'type'        => 'TECNOMECANICA',
            'expiry_date' => now()->addDays(15)->toDateString(),
        ]);
        $valid = \App\Models\Document::factory()->create([
            'vehicle_id'  => $vehicle->id,
            'type'        => 'LICENCIA',
            'expiry_date' => now()->addDays(120)->toDateString(),
        ]);

        $this->assertEquals('VENCIDO',      $expired->status);
        $this->assertEquals('VENCE_PRONTO', $soon->status);
        $this->assertEquals('VIGENTE',      $valid->status);
    }
}
```

### tests/Feature/AuthTest.php

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
}
```

---

## 20. Variables de Entorno y README

### .env.example

```dotenv
APP_NAME=ChocApp
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Base de datos
DB_CONNECTION=mysql
DB_HOST=chocapp_db
DB_PORT=3306
DB_DATABASE=chocapp
DB_USERNAME=chocapp_user
DB_PASSWORD=
DB_ROOT_PASSWORD=

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=chocapp_redis
REDIS_PASSWORD=
REDIS_PORT=6379

# Queue & Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Almacenamiento — S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=chocapp-storage
AWS_URL=
AWS_ENDPOINT=

# Firebase Cloud Messaging
FCM_PROJECT_ID=
FCM_SERVER_KEY=

# Correo
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@chocapp.com
MAIL_FROM_NAME=ChocApp

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost

# Swagger (solo non-production)
L5_SWAGGER_GENERATE_ALWAYS=false
L5_SWAGGER_CONST_HOST=http://localhost/api/v1

# Docker (docker-compose.yml)
CONTAINER_PREFIX=chocapp_local
EXPOSED_PORT=9090
```

### README.md

```markdown
# ChocApp API

REST API para documentación de accidentes de tránsito — Colombia.

## Stack
- **Laravel 11** · PHP 8.3 · MySQL 8 · Redis 7 · Nginx 1.25
- **Auth:** Laravel Sanctum (token-based, mobile)
- **Storage:** AWS S3
- **Push:** Firebase Cloud Messaging
- **PDF:** barryvdh/laravel-dompdf
- **Docs:** L5-Swagger (OpenAPI 3.0)
- **CI/CD:** Jenkins · Docker Compose

## Setup local

### 1. Clonar y configurar
git clone https://github.com/tu-org/chocapp.git
cd chocapp
cp .env.example .env

### 2. Ajustar .env
Completar: DB_PASSWORD, DB_ROOT_PASSWORD, REDIS_PASSWORD,
AWS_*, FCM_*, MAIL_*

### 3. Levantar con Docker
docker compose --project-name chocapp_local up --build -d

### 4. Instalar dependencias y migrar
docker exec chocapp_local_app composer install
docker exec chocapp_local_app php artisan key:generate
docker exec chocapp_local_app php artisan migrate --seed
docker exec chocapp_local_app php artisan l5-swagger:generate

### 5. Permisos de storage
docker exec chocapp_local_app chown -R www-data:www-data storage bootstrap/cache

## URLs

| Entorno     | URL                              |
|-------------|----------------------------------|
| Local       | http://localhost:9090/api/v1     |
| Swagger UI  | http://localhost:9090/api/documentation |
| Staging     | https://stg.chocapp.com/api/v1   |
| Production  | https://api.chocapp.com/api/v1   |

## Comandos útiles

# Limpiar cache
docker exec chocapp_local_app php artisan optimize:clear

# Correr tests
docker exec chocapp_local_app php artisan test --parallel

# Generar Swagger
docker exec chocapp_local_app php artisan l5-swagger:generate

# Ver logs
docker logs chocapp_local_app -f

# Queue worker manual
docker exec chocapp_local_app php artisan queue:work

## Jenkins

1. Crear credencial tipo **Secret File** con ID `chocapp`
   (copiar variables de `.env.example` con valores reales).
2. Agregar el repositorio en Jenkins apuntando al `Jenkinsfile` en la raíz.
3. Ramas reconocidas:
   - `staging` → deploy automático a stg.chocapp.com
   - `master`  → deploy a producción (requiere marcar `DEPLOY_TO_PRODUCTION`)

## MCP

El archivo `mcp/chocapp-api.json` define todos los endpoints para
integración con asistentes de IA (Claude, etc).
Ubicación: `/chocapp/mcp/chocapp-api.json`

## Seguridad

- Todos los endpoints protegidos requieren `Authorization: Bearer {token}`
- Rate limiting: 60 req/min general · 5 req/min auth · 10 req/min operaciones pesadas
- Headers OWASP aplicados en cada respuesta
- Archivos almacenados fuera de public/, acceso solo via S3 signed URLs
- Tokens Sanctum expiran a los 90 días

## Licencia
Propiedad de ChocApp SAS. Uso interno.
```

---

*Fin del documento — ChocApp Technical Specification v1.0.0*
