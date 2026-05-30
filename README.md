# ChocApp API

> REST API para documentación de accidentes de tránsito — Colombia 🇨🇴

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/License-Propietario-red)](LICENSE)

---

## Tabla de Contenidos

1. [Descripción](#descripción)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Arquitectura](#arquitectura)
4. [Modelo de Base de Datos](#modelo-de-base-de-datos)
5. [Estructura del Proyecto](#estructura-del-proyecto)
6. [Endpoints API](#endpoints-api)
7. [Autenticación](#autenticación)
8. [Rate Limiting](#rate-limiting)
9. [Notificaciones Push (FCM)](#notificaciones-push-fcm)
10. [Almacenamiento (S3)](#almacenamiento-s3)
11. [Generación de PDF](#generación-de-pdf)
12. [Tareas Programadas](#tareas-programadas)
13. [Configuración Local](#configuración-local)
14. [Docker Compose](#docker-compose)
15. [Variables de Entorno](#variables-de-entorno)
16. [CI/CD con Jenkins](#cicd-con-jenkins)
17. [Tests](#tests)
18. [Seguridad](#seguridad)
19. [MCP — Integración con IA](#mcp--integración-con-ia)
20. [Ambientes](#ambientes)

---

## Descripción

ChocApp es una plataforma móvil que permite a conductores colombianos **registrar, documentar y gestionar accidentes de tránsito** de forma digital. Esta API REST es el backend único que alimenta la aplicación móvil.

**Funcionalidades principales:**

- Registro de accidentes con evidencia fotográfica multiángulo (mínimo 4 fotos)
- Gestión de vehículos y sus documentos legales (SOAT, Tecnomecánica, Licencia)
- Control de vencimiento de documentos con alertas automáticas
- Historial de mantenimiento por vehículo
- Generación de reportes PDF oficiales por accidente
- Notificaciones push vía Firebase Cloud Messaging
- Registro de terceros involucrados (vehículos, peatones, ciclistas)
- Contactos de emergencia y talleres geolocalizados

---

## Stack Tecnológico

| Componente | Tecnología | Versión |
|---|---|---|
| Framework | Laravel | 12.x |
| Lenguaje | PHP | 8.3 |
| Base de datos | MySQL | 8.0 |
| Caché / Colas | Redis | 7 |
| Servidor web | Nginx | 1.25 |
| Contenedores | Docker + Compose | - |
| Autenticación | Laravel Sanctum | 4.x |
| Push notifications | Firebase Cloud Messaging | v1 |
| Almacenamiento | AWS S3 | - |
| PDF | barryvdh/laravel-dompdf | 3.x |
| Documentación API | L5-Swagger (OpenAPI 3.0) | 9.x |
| CI/CD | Jenkins | - |

---

## Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                        Nginx (reverse proxy)                 │
│           chocapp.reddantechnology.com → :9091              │
└────────────────────────────┬────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────┐
│                     Laravel 12 App (PHP-FPM)                 │
│                                                              │
│  Routes → Middleware → Controller → Service → Repository     │
│                                    ↓                         │
│                              Eloquent ORM                    │
└──────┬──────────────┬──────────────┬────────────────────────┘
       │              │              │
  ┌────▼────┐   ┌─────▼─────┐  ┌───▼──────┐
  │ MySQL 8 │   │  Redis 7  │  │  AWS S3  │
  │(datos)  │   │(cache/q)  │  │(archivos)│
  └─────────┘   └─────┬─────┘  └──────────┘
                      │
            ┌─────────▼─────────┐
            │  Queue Worker     │
            │  (notifications,  │
            │   pdf, default)   │
            └───────────────────┘
```

### Patrones de diseño

- **Repository Pattern** — abstracción de acceso a datos con interfaces
- **Service Layer** — lógica de negocio desacoplada de controladores
- **DTO (Data Transfer Objects)** — objetos tipados para transferencia de datos entre capas
- **API Resources** — transformación consistente de respuestas JSON
- **Policy-based Authorization** — autorización a nivel de modelo
- **Event / Listener** — eventos desacoplados (ej: `IncidentCreated`)

---

## Modelo de Base de Datos

### Diagrama de relaciones

```
users
  ├── vehicles (1:N)
  │     ├── documents (1:N)  [SOAT, TECNOMECANICA, LICENCIA]
  │     └── maintenance_records (1:N)
  ├── incidents (1:N)
  │     ├── incident_photos (1:N)
  │     └── third_parties (1:N)
  └── notifications (1:N)
```

### Tablas

#### `users`
| Columna | Tipo | Descripción |
|---|---|---|
| id | CHAR(36) UUID | PK |
| name | VARCHAR(100) | Nombre completo |
| email | VARCHAR(150) | Único |
| password | VARCHAR(255) | Hash bcrypt |
| id_type | ENUM | CC, CE, PPT, PASAPORTE |
| id_number | VARCHAR(30) | Único |
| phone_number | VARCHAR(20) | - |
| profile_pic_url | VARCHAR(500) | URL en S3 |
| terms_accepted | TINYINT(1) | - |
| social_provider | VARCHAR(20) | google, apple, facebook |
| social_id | VARCHAR(255) | ID del proveedor |
| fcm_token | VARCHAR(500) | Token push FCM |
| deleted_at | TIMESTAMP | SoftDelete |

#### `vehicles`
| Columna | Tipo | Descripción |
|---|---|---|
| id | UUID | PK |
| user_id | UUID | FK → users |
| plate | VARCHAR(10) | Único |
| brand / model | VARCHAR(60) | - |
| year | SMALLINT | - |
| color | VARCHAR(40) | - |
| type | ENUM | MOTOCICLETA, AUTOMOVIL |
| photo_url | VARCHAR(500) | URL en S3 |

#### `documents`
| Columna | Tipo | Descripción |
|---|---|---|
| id | UUID | PK |
| vehicle_id | UUID | FK → vehicles |
| type | ENUM | SOAT, TECNOMECANICA, LICENCIA |
| document_number | VARCHAR(60) | - |
| issue_date | DATE | Fecha expedición |
| expiry_date | DATE | Fecha vencimiento |
| pdf_url | VARCHAR(500) | URL en S3 |
| **status** | *Accessor* | VIGENTE \| VENCE_PRONTO \| VENCIDO |

> `status` es un **accessor calculado** (no columna), basado en `expiry_date`:
> - `VENCIDO` — expiró
> - `VENCE_PRONTO` — vence en ≤ 30 días
> - `VIGENTE` — más de 30 días

#### `incidents`
| Columna | Tipo | Descripción |
|---|---|---|
| id | UUID | PK |
| user_id / vehicle_id | UUID | FKs |
| title | VARCHAR(200) | Auto-generado si no se envía |
| description | TEXT | Min 10 caracteres |
| incident_date | DATE | No puede ser futura |
| incident_time | TIME | Formato HH:mm |
| location_address | VARCHAR(500) | - |
| latitude / longitude | DECIMAL | Coordenadas GPS |
| weather_condition | ENUM | SOLEADO, LLUVIOSO, NUBLADO, NOCHE |
| road_condition | ENUM | BUEN_ESTADO, HUMEDO, HUECOS, DERRUMBE |
| police_report_number | VARCHAR(60) | Opcional |
| status | ENUM | BORRADOR, REPORTADO, EN_REVISION, FINALIZADO |
| report_pdf_url | VARCHAR(500) | URL en S3 (generado bajo demanda) |

#### `incident_photos`
Ángulos disponibles: `FRONT`, `FRONT_RIGHT`, `RIGHT`, `REAR_RIGHT`, `REAR`, `REAR_LEFT`, `LEFT`, `FRONT_LEFT`, `INTERIOR`, `ODOMETER`, `EXTRA`

#### `maintenance_records`
Tipos: `ACEITE`, `FRENOS`, `LLANTAS`, `BATERIA`, `FILTROS`, `SUSPENSION`, `REVISION_GENERAL`, `OTRO`

---

## Estructura del Proyecto

```
chocapp-api/
├── app/
│   ├── Console/Commands/
│   │   ├── SendDocumentExpiryAlerts.php   # chocapp:document-expiry-alerts
│   │   └── SendMaintenanceReminders.php   # chocapp:maintenance-reminders
│   ├── DTOs/
│   │   ├── Auth/RegisterDTO.php
│   │   ├── Incident/CreateIncidentDTO.php
│   │   └── Vehicle/CreateVehicleDTO.php
│   ├── Enums/
│   │   ├── DocumentTypeEnum.php
│   │   ├── IncidentStatusEnum.php
│   │   ├── NotificationTypeEnum.php
│   │   └── VehicleTypeEnum.php
│   ├── Events/
│   │   └── IncidentCreated.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── IncidentController.php
│   │   │   ├── MaintenanceController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── SupportController.php
│   │   │   └── VehicleController.php
│   │   ├── Middleware/
│   │   │   ├── ForceJsonResponse.php
│   │   │   ├── RateLimitByUser.php
│   │   │   └── SecurityHeaders.php
│   │   ├── Requests/                      # Form Requests con validación
│   │   └── Resources/                     # API Resources (transformación JSON)
│   ├── Listeners/
│   │   └── NotifyInsuranceOnIncident.php  # Queued
│   ├── Models/
│   │   ├── Document.php                   # Accessor: status
│   │   ├── Incident.php                   # Accessor: cover_photo_url
│   │   ├── IncidentPhoto.php
│   │   ├── MaintenanceRecord.php
│   │   ├── Notification.php
│   │   ├── ThirdParty.php
│   │   ├── User.php
│   │   └── Vehicle.php
│   ├── Policies/
│   │   ├── DocumentPolicy.php
│   │   ├── IncidentPolicy.php
│   │   └── VehiclePolicy.php
│   ├── Repositories/
│   │   ├── Contracts/                     # Interfaces para DI
│   │   ├── IncidentRepository.php
│   │   └── UserRepository.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── DocumentStatusService.php
│   │   ├── FcmNotificationService.php
│   │   ├── IncidentService.php
│   │   ├── PdfReportService.php
│   │   ├── SocialAuthService.php
│   │   └── StorageService.php
│   └── Traits/
│       └── ApiResponseTrait.php
├── database/
│   ├── factories/                         # UserFactory, VehicleFactory, etc.
│   ├── migrations/                        # 11 migraciones
│   └── seeders/
├── docker-files/
│   ├── mysql/my.cnf
│   ├── nginx/site.conf
│   └── php/php.ini
├── mcp/
│   └── chocapp-api.json                   # MCP para integración con IA
├── resources/views/pdf/
│   └── incident-report.blade.php         # Template PDF oficial
├── routes/
│   ├── api.php                            # 35 endpoints
│   └── console.php                        # Scheduler
├── tests/Feature/
│   ├── AuthTest.php                       # 6 casos
│   └── IncidentTest.php                   # 5 casos
├── docker-compose.yml
├── Dockerfile
└── Jenkinsfile
```

---

## Endpoints API

**Base URL:** `https://chocapp.reddantechnology.com/api/v1`

**Swagger UI (staging):** `https://stg.chocapp.reddantechnology.com/api/documentation`

### Formato de respuesta estándar

```json
// Éxito
{
  "success": true,
  "message": "Operación exitosa",
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}

// Error
{
  "success": false,
  "message": "Error de validación.",
  "errors": { "email": ["El correo ya está en uso."] },
  "code": 422
}
```

### Auth

| Método | Endpoint | Auth | Descripción |
|---|---|---|---|
| POST | `/auth/register` | No | Registro con foto opcional |
| POST | `/auth/login` | No | Login, retorna Bearer token |
| POST | `/auth/social` | No | Login Google / Apple / Facebook |
| POST | `/auth/logout` | Sí | Revoca el token actual |
| GET | `/auth/me` | Sí | Perfil del usuario con vehículos |
| PUT | `/auth/profile` | Sí | Actualizar nombre, teléfono, foto, FCM token |
| POST | `/auth/password/forgot` | No | Solicitar reset de contraseña |
| POST | `/auth/password/reset` | No | Restablecer contraseña |

### Vehículos

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/vehicles` | Listar mis vehículos |
| POST | `/vehicles` | Registrar vehículo (con foto opcional) |
| GET | `/vehicles/{id}` | Detalle con documentos y mantenimientos |
| PUT | `/vehicles/{id}` | Actualizar datos |
| DELETE | `/vehicles/{id}` | Soft delete |

### Documentos (anidados bajo vehículo)

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/vehicles/{id}/documents` | Listar documentos con status calculado |
| POST | `/vehicles/{id}/documents` | Crear o actualizar un documento (upsert por tipo) |
| DELETE | `/vehicles/{id}/documents/{doc}` | Eliminar documento |

### Incidentes

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/incidents` | Lista paginada (filtros: status, from, to) |
| POST | `/incidents` | Registrar accidente con mínimo 4 fotos |
| GET | `/incidents/{id}` | Detalle completo con fotos y terceros |
| PUT | `/incidents/{id}` | Actualizar estado / datos |
| DELETE | `/incidents/{id}` | Eliminar con fotos (soft delete) |
| GET | `/incidents/{id}/export-pdf` | Generar / descargar PDF oficial |
| POST | `/incidents/{id}/photos` | Agregar fotos a incidente existente |
| DELETE | `/incidents/{id}/photos/{photo}` | Eliminar foto |
| POST | `/incidents/{id}/third-parties` | Agregar tercero involucrado |

### Mantenimiento (anidado bajo vehículo)

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/vehicles/{id}/maintenance` | Historial ordenado por fecha |
| POST | `/vehicles/{id}/maintenance` | Registrar mantenimiento |
| PUT | `/vehicles/{id}/maintenance/{record}` | Actualizar registro |
| DELETE | `/vehicles/{id}/maintenance/{record}` | Eliminar |

### Notificaciones

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/notifications` | Lista paginada (filtro: unread_only) |
| PATCH | `/notifications/read-all` | Marcar todas como leídas |
| PATCH | `/notifications/{id}/read` | Marcar una como leída |
| DELETE | `/notifications/{id}` | Eliminar |

### Soporte

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/support/emergency-contacts` | Números de emergencia Colombia (123, etc.) |
| GET | `/support/workshops` | Talleres cercanos por coordenada |

---

## Autenticación

ChocApp usa **Laravel Sanctum** con tokens Bearer de duración configurable (default: 90 días).

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Registro:**
```bash
curl -X POST https://chocapp.reddantechnology.com/api/v1/auth/register \
  -F "name=Carlos Rodríguez" \
  -F "email=carlos@example.com" \
  -F "password=Passw0rd!" \
  -F "password_confirmation=Passw0rd!" \
  -F "id_type=CC" \
  -F "id_number=1234567890" \
  -F "phone_number=+573001234567" \
  -F "terms_accepted=1"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Registro exitoso",
  "data": {
    "user": { "id": "uuid", "name": "Carlos Rodríguez", "email": "..." },
    "token": "1|abc123..."
  }
}
```

---

## Rate Limiting

| Grupo | Límite | Aplica a |
|---|---|---|
| `api` | 60 req/min | Todos los endpoints autenticados (por usuario/IP) |
| `auth` | 5 req/min | `/auth/register`, `/auth/login`, `/auth/social` |
| `heavy` | 10 req/min | Export PDF, carga de fotos |

Respuesta al superar el límite: HTTP `429` con mensaje `"Demasiadas solicitudes. Intenta más tarde."`

---

## Notificaciones Push (FCM)

El servicio `FcmNotificationService` persiste cada notificación en la tabla `notifications` (independientemente del resultado del push) y luego envía a FCM si el usuario tiene `fcm_token`.

**Tipos de notificación:**

| Tipo | Descripción |
|---|---|
| `INCIDENT_UPDATE` | Cambio de estado en un accidente |
| `DOCUMENT_EXPIRING` | Documento próximo a vencer |
| `MAINTENANCE_REMINDER` | Recordatorio de mantenimiento |
| `ALERT` | Alerta general |
| `INFO` | Información general |

**Actualizar FCM token del dispositivo:**
```bash
PUT /api/v1/auth/profile
{ "fcm_token": "token-del-dispositivo" }
```

---

## Almacenamiento (S3)

Todos los archivos se almacenan en **AWS S3** mediante `StorageService`. Los nombres se generan con UUID para evitar colisiones y nunca se confía en el nombre original del archivo.

**Rutas en S3:**

| Tipo | Ruta |
|---|---|
| Fotos de perfil | `users/avatars/{uuid}.ext` |
| Fotos de vehículo | `vehicles/photos/{uuid}.ext` |
| Fotos de incidente | `incidents/{incident_id}/photos/{uuid}.ext` |
| PDF de incidente | `incidents/{incident_id}/reports/{uuid}.pdf` |
| PDFs de documentos | `vehicles/{vehicle_id}/documents/{uuid}.pdf` |

---

## Generación de PDF

`PdfReportService` genera reportes oficiales de accidente usando **DomPDF** sobre la plantilla Blade `resources/views/pdf/incident-report.blade.php`.

- Si el PDF ya fue generado, retorna la URL cacheada en `incidents.report_pdf_url`
- El PDF incluye: datos del accidente, vehículo, conductor, terceros y galería fotográfica
- Se sube automáticamente a S3 y se retorna la URL para descarga

```bash
GET /api/v1/incidents/{id}/export-pdf
Authorization: Bearer {token}
# Retorna: Content-Type: application/pdf (stream download)
```

---

## Tareas Programadas

Definidas en `routes/console.php` (Laravel 12 scheduler):

| Comando | Frecuencia | Descripción |
|---|---|---|
| `chocapp:document-expiry-alerts` | Diario 8:00 AM | Alerta push por documentos que vencen en ≤ 30 días |
| `chocapp:maintenance-reminders` | Lunes 9:00 AM | Recordatorio de mantenimientos programados en los próximos 7 días |
| `sanctum:prune-expired --hours=2160` | Diario | Limpia tokens expirados (90 días) |
| `queue:prune-failed --hours=168` | Semanal | Limpia jobs fallidos con más de 7 días |

---

## Configuración Local

### Prerequisitos
- PHP 8.3 + extensiones: `pdo_mysql`, `redis`, `gd`, `zip`, `mbstring`, `intl`, `bcmath`
- Composer 2.x
- MySQL 8.0
- Redis 7

### Instalación manual

```bash
# 1. Clonar
git clone https://github.com/IngDanielleon/chocapp-api.git
cd chocapp-api
git checkout staging

# 2. Dependencias
composer install

# 3. Entorno
cp .env.example .env
# Editar .env con tus credenciales de DB, Redis, S3, FCM

# 4. Clave de aplicación
php artisan key:generate

# 5. Migraciones
php artisan migrate

# 6. Swagger
php artisan l5-swagger:generate

# 7. Servidor de desarrollo
php artisan serve
```

### Instalación con Docker (recomendado)

```bash
# 1. Clonar
git clone https://github.com/IngDanielleon/chocapp-api.git
cd chocapp-api
git checkout staging

# 2. Configurar variables de entorno Docker
cp .env.example .env
# Ajustar: DB_PASSWORD, DB_ROOT_PASSWORD, REDIS_PASSWORD, AWS_*, FCM_*

# 3. Levantar todos los servicios
docker compose --project-name chocapp_local up --build -d

# 4. Esperar que el contenedor app esté healthy, luego:
docker exec chocapp_local_app chown -R www-data:www-data storage bootstrap/cache
docker exec chocapp_local_app composer install --no-interaction --optimize-autoloader
docker exec chocapp_local_app php artisan key:generate
docker exec chocapp_local_app php artisan migrate --seed
docker exec chocapp_local_app php artisan l5-swagger:generate
```

**Servicios disponibles:**

| Servicio | URL / Puerto |
|---|---|
| API | `http://localhost:9090/api/v1` |
| Swagger UI | `http://localhost:9090/api/documentation` |
| Health check | `http://localhost:9090/up` |

---

## Docker Compose

El stack levanta 6 contenedores:

| Contenedor | Imagen | Descripción |
|---|---|---|
| `{prefix}_app` | PHP 8.3-FPM (custom) | Aplicación Laravel |
| `{prefix}_nginx` | nginx:1.25-alpine | Servidor web / proxy |
| `{prefix}_db` | mysql:8.0 | Base de datos |
| `{prefix}_redis` | redis:7-alpine | Caché y colas |
| `{prefix}_queue` | PHP 8.3-FPM (custom) | Worker de colas |
| `{prefix}_scheduler` | PHP 8.3-FPM (custom) | Scheduler (cron cada 60s) |

---

## Variables de Entorno

```dotenv
# Aplicación
APP_NAME=ChocApp
APP_ENV=local|staging|production
APP_KEY=                          # php artisan key:generate
APP_URL=https://chocapp.reddantechnology.com
APP_TIMEZONE=America/Bogota

# Base de datos MySQL
DB_HOST=chocapp_db
DB_DATABASE=chocapp
DB_USERNAME=chocapp_user
DB_PASSWORD=                      # REQUERIDO
DB_ROOT_PASSWORD=                 # REQUERIDO (para Docker)

# Redis
REDIS_HOST=chocapp_redis
REDIS_PASSWORD=                   # REQUERIDO

# AWS S3
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=                # REQUERIDO
AWS_SECRET_ACCESS_KEY=            # REQUERIDO
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=chocapp-storage

# Firebase Cloud Messaging
FCM_PROJECT_ID=                   # REQUERIDO para push
FCM_SERVER_KEY=                   # REQUERIDO para push

# Correo
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@chocapp.reddantechnology.com

# Swagger (desactivar en producción)
L5_SWAGGER_GENERATE_ALWAYS=false
L5_SWAGGER_CONST_HOST=https://chocapp.reddantechnology.com/api/v1

# Docker
CONTAINER_PREFIX=chocapp_local
EXPOSED_PORT=9090
```

---

## CI/CD con Jenkins

El `Jenkinsfile` en la raíz define el pipeline completo:

### Configuración requerida

1. Crear credencial tipo **Secret File** con ID `chocapp` en Jenkins (copiar `.env.example` con valores reales de cada ambiente).
2. Agregar el repositorio en Jenkins apuntando al `Jenkinsfile`.

### Ramas y ambientes

| Rama | Ambiente | Dominio | Puerto |
|---|---|---|---|
| `staging` | Staging | `stg.chocapp.reddantechnology.com` | 9092 |
| `master` | Producción | `chocapp.reddantechnology.com` | 9091 |

### Etapas del pipeline

```
Load credentials → Security scan → ENV setup →
Port validation → Virtualhost setup → Deploy
```

### Deploy staging (automático al push a `staging`)
```groovy
// Ejecuta: migrate:fresh --seed + optimize + l5-swagger:generate
```

### Deploy producción (manual — requiere marcar `DEPLOY_TO_PRODUCTION=true`)
```groovy
// Ejecuta: migrate --force + config:cache + route:cache + view:cache + optimize
// Swagger NO se genera en producción
```

---

## Tests

```bash
# Ejecutar todos los tests
php artisan test

# Con Docker
docker exec chocapp_local_app php artisan test --parallel

# Solo una suite
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/IncidentTest.php
```

### Cobertura actual

**`AuthTest`** (6 casos):
- ✅ Registro exitoso de usuario
- ✅ Login con credenciales correctas retorna token
- ✅ Request sin autenticar retorna 401
- ✅ Email duplicado retorna 422
- ✅ Credenciales incorrectas retornan 401
- ✅ Usuario autenticado obtiene su perfil
- ✅ Logout invalida el token

**`IncidentTest`** (5 casos):
- ✅ Crear incidente con mínimo 4 fotos (storage fake)
- ✅ Validación mínimo 4 fotos retorna 422
- ✅ Usuario no puede ver incidente de otro usuario (403)
- ✅ Request sin autenticar retorna 401
- ✅ Accessor de status de documento (VIGENTE / VENCE_PRONTO / VENCIDO)

---

## Seguridad

- **Headers OWASP** aplicados en cada respuesta via `SecurityHeaders` middleware:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: DENY`
  - `Strict-Transport-Security: max-age=31536000`
  - `Content-Security-Policy: default-src 'none'`
- **Todos los endpoints** protegidos por Sanctum requieren `Authorization: Bearer {token}`
- **Rate limiting** por usuario/IP para prevenir brute force y abuso
- **Archivos** almacenados fuera de `public/`, acceso solo via URLs de S3
- **UUIDs** en todas las PKs — nunca IDs secuenciales expuestos
- **Validación estricta** en todos los Form Requests
- **Políticas** verifican ownership antes de cualquier operación sobre recursos
- **SoftDeletes** en usuarios, vehículos e incidentes
- **Tokens Sanctum** con expiración a 90 días

---

## MCP — Integración con IA

El archivo `mcp/chocapp-api.json` define todos los endpoints para integración con asistentes de IA (Claude, etc.) siguiendo el **Model Context Protocol**.

```json
{
  "name": "chocapp-api",
  "base_url": "https://chocapp.reddantechnology.com/api/v1",
  "authentication": { "type": "bearer", "obtain_via": "POST /auth/login" },
  "tools": [ ... ]
}
```

Para usarlo en Claude Code, agregar el servidor MCP apuntando a `mcp/chocapp-api.json`.

---

## Ambientes

| Ambiente | URL API | Swagger | Rama Git |
|---|---|---|---|
| Local | `http://localhost:9090/api/v1` | `http://localhost:9090/api/documentation` | `staging` |
| Staging | `https://stg.chocapp.reddantechnology.com/api/v1` | `/api/documentation` | `staging` |
| Producción | `https://chocapp.reddantechnology.com/api/v1` | Deshabilitado | `master` |

### Comandos útiles

```bash
# Limpiar caché
docker exec chocapp_local_app php artisan optimize:clear

# Regenerar Swagger
docker exec chocapp_local_app php artisan l5-swagger:generate

# Ver logs en tiempo real
docker logs chocapp_local_app -f

# Ejecutar comando artisan
docker exec chocapp_local_app php artisan {comando}

# Acceder al contenedor
docker exec -it chocapp_local_app sh

# Ver estado de la cola
docker exec chocapp_local_app php artisan queue:monitor redis:notifications,pdf,default

# Probar alertas manualmente
docker exec chocapp_local_app php artisan chocapp:document-expiry-alerts
docker exec chocapp_local_app php artisan chocapp:maintenance-reminders
```

---

## Licencia

Propiedad de **Redd An Technology** — Todos los derechos reservados.

© 2026 ChocApp / Redd An Technology
