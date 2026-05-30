# ChocApp — Análisis de Brechas API ↔ App Android

> **Propósito:** Documentar los ajustes, campos faltantes y recursos nuevos que la app Android debe implementar o corregir para integrarse correctamente con la API REST descrita en `api-technical.md`.
>
> **Fecha de análisis:** 2026-05-30  
> **Autor:** Generado con Claude Code

---

## Tabla de Contenidos

1. [Resumen ejecutivo](#1-resumen-ejecutivo)
2. [Modelo de dominio — Campos faltantes o incorrectos](#2-modelo-de-dominio--campos-faltantes-o-incorrectos)
3. [Interfaz Retrofit — Endpoints faltantes](#3-interfaz-retrofit--endpoints-faltantes)
4. [Capa de respuesta — Wrapper estándar ignorado](#4-capa-de-respuesta--wrapper-estándar-ignorado)
5. [DTOs de solicitud faltantes](#5-dtos-de-solicitud-faltantes)
6. [Repositorios y Use Cases faltantes](#6-repositorios-y-use-cases-faltantes)
7. [ViewModels faltantes](#7-viewmodels-faltantes)
8. [Pantallas y flujos faltantes](#8-pantallas-y-flujos-faltantes)
9. [Integración FCM (notificaciones push)](#9-integración-fcm-notificaciones-push)
10. [Carga de fotos — Multipart form data](#10-carga-de-fotos--multipart-form-data)
11. [Paginación ignorada](#11-paginación-ignorada)
12. [Mapeo de ángulos de foto](#12-mapeo-de-ángulos-de-foto)
13. [Corrección de URL base](#13-corrección-de-url-base)
14. [Prioridad de implementación](#14-prioridad-de-implementación)

---

## 1. Resumen ejecutivo

| Categoría | Estado actual | Ajuste requerido |
|---|---|---|
| Endpoints en `ChocAppApi.kt` | 4 de 35 | Implementar 31 endpoints faltantes |
| Wrapper de respuesta API | No implementado | Añadir clase `ApiResponse<T>` |
| Modelos de dominio | Incompletos / enums incorrectos | Actualizar 4 modelos |
| DTOs de request | Ninguno | Crear 6 DTOs |
| Repositorios | 1 (solo vehículos) | Crear 5 repositorios adicionales |
| Use Cases | 1 (GetVehicles) | Crear ~12 use cases |
| ViewModels | 2 | Crear 5 ViewModels adicionales |
| Pantallas nuevas | 0 | Crear 4 pantallas |
| FCM integration | No integrada | Configurar Firebase Messaging |
| Carga de fotos multipart | No implementada | Implementar upload multipart |
| Paginación | No manejada | Implementar paginación en listas |

---

## 2. Modelo de dominio — Campos faltantes o incorrectos

### 2.1 `User.kt` — Ajustes requeridos

**Problema:** La API usa un solo campo `name` (VARCHAR 100), pero el modelo Android lo divide en `firstName` + `lastName`. Además faltan campos críticos.

```kotlin
// ACTUAL (incorrecto)
data class User(
    val firstName: String,   // ❌ API solo tiene "name"
    val lastName: String,    // ❌ API solo tiene "name"
    ...
)

// CORRECTO
data class User(
    val id: String,
    val name: String,               // ✅ Alineado con API
    val email: String,
    val phoneNumber: String,
    val idType: IdType,             // ✅ Usar enum tipado
    val idNumber: String,
    val profilePicUrl: String? = null,
    val termsAccepted: Boolean,
    val fcmToken: String? = null,   // ✅ NUEVO — requerido para push notifications
    val socialProvider: String? = null, // ✅ NUEVO — google / apple / facebook
    val socialId: String? = null    // ✅ NUEVO — ID del proveedor social
)

enum class IdType { CC, CE, PPT, PASAPORTE }
```

---

### 2.2 `Vehicle.kt` — Campos faltantes

```kotlin
// ACTUAL (incompleto)
data class Vehicle(
    val id: String,
    val plate: String,
    val brand: String,
    val model: String,
    val year: Int,
    val color: String,
    val insuranceStatus: InsuranceStatus  // ❌ No existe en la tabla vehicles
)

// CORRECTO
data class Vehicle(
    val id: String,
    val userId: String,
    val plate: String,
    val brand: String,
    val model: String,
    val year: Int,
    val color: String,
    val type: VehicleType,           // ✅ NUEVO — MOTOCICLETA / AUTOMOVIL
    val photoUrl: String? = null,    // ✅ NUEVO — foto del vehículo en S3
    val documents: List<Document> = emptyList()  // ✅ incluido en GET /vehicles/{id}
    // insuranceStatus se DERIVA del documento SOAT más reciente, no es campo directo
)

enum class VehicleType { MOTOCICLETA, AUTOMOVIL }
```

> **Nota:** `insuranceStatus` no es un campo de la tabla `vehicles` en la API. Es un valor derivado del accessor del documento SOAT activo. Se debe calcular en la app a partir de `documents`.

---

### 2.3 `Incident.kt` — Campos faltantes y enum incorrecto

**Problema crítico:** El enum `IncidentStatus` no coincide con los valores de la API.

```kotlin
// ACTUAL — enum INCORRECTO
enum class IncidentStatus {
    PENDING,      // ❌ No existe en la API
    IN_PROGRESS,  // ❌ No existe en la API
    RESOLVED,     // ❌ No existe en la API
    CANCELLED     // ❌ No existe en la API
}

// CORRECTO — alineado con API
enum class IncidentStatus {
    BORRADOR,     // ✅
    REPORTADO,    // ✅
    EN_REVISION,  // ✅
    FINALIZADO    // ✅
}
```

**Campos faltantes en el modelo:**

```kotlin
// CORRECTO
data class Incident(
    val id: String,
    val vehicleId: String,
    val title: String,
    val description: String,
    val incidentDate: String,          // ✅ NUEVO — formato "YYYY-MM-DD"
    val incidentTime: String,          // ✅ NUEVO — formato "HH:mm"
    val locationAddress: String,
    val latitude: Double? = null,      // ✅ NUEVO — coordenadas GPS
    val longitude: Double? = null,     // ✅ NUEVO — coordenadas GPS
    val weatherCondition: WeatherCondition,
    val roadCondition: RoadCondition,
    val policeReportNumber: String? = null,  // ✅ NUEVO — número de denuncio
    val status: IncidentStatus,
    val photos: List<IncidentPhoto> = emptyList(),
    val thirdParties: List<ThirdParty> = emptyList(),  // ✅ NUEVO
    val reportPdfUrl: String? = null,  // ✅ NUEVO — URL PDF generado en S3
    val coverPhotoUrl: String? = null  // ✅ NUEVO — accessor en el modelo API
)

enum class WeatherCondition { SOLEADO, LLUVIOSO, NUBLADO, NOCHE }
enum class RoadCondition    { BUEN_ESTADO, HUMEDO, HUECOS, DERRUMBE }
```

---

### 2.4 `IncidentReport.kt` — Campos faltantes para el flujo de creación

```kotlin
// ACTUAL (incompleto)
data class IncidentReport(
    var title: String = "",
    var description: String = "",
    var weather: String = "",
    var roadState: String = "",
    var location: String = "Calle 100 #15-30, Bogotá",
    var photoUris: List<Uri> = emptyList()
)

// CORRECTO
data class IncidentReport(
    var vehicleId: String = "",        // ✅ NUEVO — requerido por la API
    var title: String = "",
    var description: String = "",
    var incidentDate: String = "",     // ✅ NUEVO — no puede ser fecha futura
    var incidentTime: String = "",     // ✅ NUEVO — formato HH:mm
    var locationAddress: String = "",
    var latitude: Double? = null,      // ✅ NUEVO — de GPS / mapa
    var longitude: Double? = null,     // ✅ NUEVO — de GPS / mapa
    var weather: WeatherCondition = WeatherCondition.SOLEADO,
    var roadState: RoadCondition = RoadCondition.BUEN_ESTADO,
    var policeReportNumber: String? = null,  // ✅ NUEVO — opcional
    var photoUris: List<Uri> = emptyList()   // Mínimo 4, máximo 11
)
```

---

### 2.5 `NotificationType.kt` — Enum desalineado

```kotlin
// ACTUAL (incorrecto)
enum class NotificationType {
    ALERT,
    INFO,
    SUCCESS,           // ❌ No existe en la API
    DOCUMENT_EXPIRING
    // ❌ Falta INCIDENT_UPDATE
    // ❌ Falta MAINTENANCE_REMINDER
}

// CORRECTO
enum class NotificationType {
    INCIDENT_UPDATE,      // ✅ NUEVO
    DOCUMENT_EXPIRING,    // ✅
    MAINTENANCE_REMINDER, // ✅ NUEVO
    ALERT,                // ✅
    INFO                  // ✅
    // SUCCESS eliminado — no existe en la API
}
```

---

### 2.6 Modelos nuevos requeridos

#### `Document.kt`

```kotlin
data class Document(
    val id: String,
    val vehicleId: String,
    val type: DocumentType,
    val documentNumber: String,
    val issueDate: String,       // "YYYY-MM-DD"
    val expiryDate: String,      // "YYYY-MM-DD"
    val pdfUrl: String? = null,
    val status: DocumentStatus   // Calculado por la API (accessor)
)

enum class DocumentType   { SOAT, TECNOMECANICA, LICENCIA }
enum class DocumentStatus { VIGENTE, VENCE_PRONTO, VENCIDO }
```

#### `IncidentPhoto.kt`

```kotlin
data class IncidentPhoto(
    val id: String,
    val incidentId: String,
    val angle: PhotoAngle,
    val photoUrl: String
)

// Mapeo exacto de los ángulos de la API
enum class PhotoAngle {
    FRONT, FRONT_RIGHT, RIGHT, REAR_RIGHT,
    REAR, REAR_LEFT, LEFT, FRONT_LEFT,
    INTERIOR, ODOMETER, EXTRA
}
```

#### `ThirdParty.kt`

```kotlin
data class ThirdParty(
    val id: String,
    val incidentId: String,
    val name: String,
    val idNumber: String? = null,
    val phone: String? = null,
    val vehiclePlate: String? = null,
    val vehicleBrand: String? = null,
    val insuranceCompany: String? = null,
    val insurancePolicy: String? = null
)
```

#### `MaintenanceRecord.kt` (dominio)

```kotlin
data class MaintenanceRecord(
    val id: String,
    val vehicleId: String,
    val type: MaintenanceType,
    val date: String,           // "YYYY-MM-DD"
    val cost: Int,              // En pesos COP
    val mileage: Int,           // Kilometraje en el momento del servicio
    val workshopName: String,
    val notes: String? = null
)

enum class MaintenanceType {
    ACEITE, FRENOS, LLANTAS, BATERIA,
    FILTROS, SUSPENSION, REVISION_GENERAL, OTRO
}
```

#### `EmergencyContact.kt` y `Workshop.kt` (para soporte)

```kotlin
data class EmergencyContact(
    val name: String,
    val number: String,
    val description: String,
    val type: String   // "emergency" | "insurance" | "legal"
)

data class Workshop(
    val id: String,
    val name: String,
    val address: String,
    val phone: String,
    val latitude: Double,
    val longitude: Double,
    val distanceKm: Double? = null
)
```

---

## 3. Interfaz Retrofit — Endpoints faltantes

**Estado actual:** Solo 4 endpoints de un total de 35.

```kotlin
// ARCHIVO ACTUAL: ChocAppApi.kt — Solo 4 endpoints
@GET("vehicles")             suspend fun getVehicles(): List<Vehicle>
@GET("vehicles/{id}")        suspend fun getVehicleById(): Vehicle
@GET("incidents")            suspend fun getIncidents(): List<Incident>
@GET("incidents/{id}")       suspend fun getIncidentById(): Incident
```

### Interfaz completa requerida

```kotlin
interface ChocAppApi {

    // ─── AUTH ───────────────────────────────────────────────
    @Multipart
    @POST("auth/register")
    suspend fun register(@PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
                         @Part profilePic: MultipartBody.Part? = null): ApiResponse<AuthData>

    @POST("auth/login")
    suspend fun login(@Body body: LoginRequest): ApiResponse<AuthData>

    @POST("auth/social")
    suspend fun socialLogin(@Body body: SocialLoginRequest): ApiResponse<AuthData>

    @POST("auth/logout")
    suspend fun logout(): ApiResponse<Unit>

    @GET("auth/me")
    suspend fun getProfile(): ApiResponse<UserWithVehicles>

    @Multipart
    @PUT("auth/profile")
    suspend fun updateProfile(@PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
                              @Part profilePic: MultipartBody.Part? = null): ApiResponse<User>

    @POST("auth/password/forgot")
    suspend fun forgotPassword(@Body body: ForgotPasswordRequest): ApiResponse<Unit>

    @POST("auth/password/reset")
    suspend fun resetPassword(@Body body: ResetPasswordRequest): ApiResponse<Unit>

    // ─── VEHÍCULOS ───────────────────────────────────────────
    @GET("vehicles")
    suspend fun getVehicles(): ApiResponse<List<Vehicle>>

    @Multipart
    @POST("vehicles")
    suspend fun createVehicle(@PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
                              @Part photo: MultipartBody.Part? = null): ApiResponse<Vehicle>

    @GET("vehicles/{id}")
    suspend fun getVehicleById(@Path("id") id: String): ApiResponse<Vehicle>

    @Multipart
    @PUT("vehicles/{id}")
    suspend fun updateVehicle(@Path("id") id: String,
                              @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
                              @Part photo: MultipartBody.Part? = null): ApiResponse<Vehicle>

    @DELETE("vehicles/{id}")
    suspend fun deleteVehicle(@Path("id") id: String): ApiResponse<Unit>

    // ─── DOCUMENTOS ──────────────────────────────────────────
    @GET("vehicles/{vehicleId}/documents")
    suspend fun getDocuments(@Path("vehicleId") vehicleId: String): ApiResponse<List<Document>>

    @Multipart
    @POST("vehicles/{vehicleId}/documents")
    suspend fun upsertDocument(
        @Path("vehicleId") vehicleId: String,
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part pdfFile: MultipartBody.Part? = null
    ): ApiResponse<Document>

    @DELETE("vehicles/{vehicleId}/documents/{docId}")
    suspend fun deleteDocument(
        @Path("vehicleId") vehicleId: String,
        @Path("docId") docId: String
    ): ApiResponse<Unit>

    // ─── INCIDENTES ──────────────────────────────────────────
    @GET("incidents")
    suspend fun getIncidents(
        @Query("status") status: String? = null,
        @Query("from") from: String? = null,
        @Query("to") to: String? = null,
        @Query("page") page: Int = 1
    ): ApiResponse<PaginatedList<Incident>>

    @Multipart
    @POST("incidents")
    suspend fun createIncident(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part photos: List<MultipartBody.Part>   // Mínimo 4
    ): ApiResponse<Incident>

    @GET("incidents/{id}")
    suspend fun getIncidentById(@Path("id") id: String): ApiResponse<Incident>

    @PUT("incidents/{id}")
    suspend fun updateIncident(@Path("id") id: String,
                               @Body body: UpdateIncidentRequest): ApiResponse<Incident>

    @DELETE("incidents/{id}")
    suspend fun deleteIncident(@Path("id") id: String): ApiResponse<Unit>

    @GET("incidents/{id}/export-pdf")
    suspend fun exportIncidentPdf(@Path("id") id: String): ResponseBody  // PDF stream

    @Multipart
    @POST("incidents/{id}/photos")
    suspend fun addPhotos(@Path("id") id: String,
                          @Part photos: List<MultipartBody.Part>): ApiResponse<List<IncidentPhoto>>

    @DELETE("incidents/{id}/photos/{photoId}")
    suspend fun deletePhoto(@Path("id") id: String,
                            @Path("photoId") photoId: String): ApiResponse<Unit>

    @POST("incidents/{id}/third-parties")
    suspend fun addThirdParty(@Path("id") id: String,
                              @Body body: CreateThirdPartyRequest): ApiResponse<ThirdParty>

    // ─── MANTENIMIENTO ───────────────────────────────────────
    @GET("vehicles/{vehicleId}/maintenance")
    suspend fun getMaintenanceHistory(
        @Path("vehicleId") vehicleId: String
    ): ApiResponse<List<MaintenanceRecord>>

    @POST("vehicles/{vehicleId}/maintenance")
    suspend fun createMaintenanceRecord(
        @Path("vehicleId") vehicleId: String,
        @Body body: CreateMaintenanceRequest
    ): ApiResponse<MaintenanceRecord>

    @PUT("vehicles/{vehicleId}/maintenance/{recordId}")
    suspend fun updateMaintenanceRecord(
        @Path("vehicleId") vehicleId: String,
        @Path("recordId") recordId: String,
        @Body body: CreateMaintenanceRequest
    ): ApiResponse<MaintenanceRecord>

    @DELETE("vehicles/{vehicleId}/maintenance/{recordId}")
    suspend fun deleteMaintenanceRecord(
        @Path("vehicleId") vehicleId: String,
        @Path("recordId") recordId: String
    ): ApiResponse<Unit>

    // ─── NOTIFICACIONES ──────────────────────────────────────
    @GET("notifications")
    suspend fun getNotifications(
        @Query("unread_only") unreadOnly: Boolean = false,
        @Query("page") page: Int = 1
    ): ApiResponse<PaginatedList<Notification>>

    @PATCH("notifications/read-all")
    suspend fun markAllAsRead(): ApiResponse<Unit>

    @PATCH("notifications/{id}/read")
    suspend fun markAsRead(@Path("id") id: String): ApiResponse<Notification>

    @DELETE("notifications/{id}")
    suspend fun deleteNotification(@Path("id") id: String): ApiResponse<Unit>

    // ─── SOPORTE ─────────────────────────────────────────────
    @GET("support/emergency-contacts")
    suspend fun getEmergencyContacts(): ApiResponse<List<EmergencyContact>>

    @GET("support/workshops")
    suspend fun getNearbyWorkshops(
        @Query("latitude") latitude: Double,
        @Query("longitude") longitude: Double
    ): ApiResponse<List<Workshop>>

    companion object {
        const val BASE_URL = "https://chocapp.reddantechnology.com/api/v1/"
    }
}
```

---

## 4. Capa de respuesta — Wrapper estándar ignorado

**Problema crítico:** Todos los endpoints devuelven un sobre JSON estándar que la app no maneja:

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}
```

**Clases requeridas:**

```kotlin
// data/remote/dto/ApiResponse.kt
data class ApiResponse<T>(
    val success: Boolean,
    val message: String,
    val data: T? = null,
    val errors: Map<String, List<String>>? = null,
    val meta: PaginationMeta? = null
)

data class PaginationMeta(
    val currentPage: Int,
    val lastPage: Int,
    val perPage: Int,
    val total: Int
)

data class PaginatedList<T>(
    val items: List<T>,
    val meta: PaginationMeta
)

// data/remote/dto/AuthData.kt
data class AuthData(
    val user: UserDto,
    val token: String
)
```

**Interceptor de autenticación requerido:**

```kotlin
// data/remote/interceptor/AuthInterceptor.kt
class AuthInterceptor(private val tokenProvider: () -> String?) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request().newBuilder()
            .addHeader("Authorization", "Bearer ${tokenProvider()}")
            .addHeader("Accept", "application/json")
            .build()
        return chain.proceed(request)
    }
}
```

---

## 5. DTOs de solicitud faltantes

Todos los campos de los `@Body` de Retrofit necesitan clases tipadas:

| DTO | Campos requeridos |
|---|---|
| `LoginRequest` | `email`, `password`, `fcm_token?` |
| `SocialLoginRequest` | `provider` (google/apple/facebook), `token`, `fcm_token?` |
| `ForgotPasswordRequest` | `email` |
| `ResetPasswordRequest` | `token`, `email`, `password`, `password_confirmation` |
| `CreateVehicleRequest` | `plate`, `brand`, `model`, `year`, `color`, `type` |
| `CreateIncidentRequest` | `vehicle_id`, `title?`, `description`, `incident_date`, `incident_time`, `location_address`, `latitude?`, `longitude?`, `weather_condition`, `road_condition`, `police_report_number?` |
| `UpdateIncidentRequest` | `title?`, `description?`, `status?`, `police_report_number?` |
| `CreateMaintenanceRequest` | `type`, `date`, `cost`, `mileage`, `workshop_name`, `notes?` |
| `CreateThirdPartyRequest` | `name`, `id_number?`, `phone?`, `vehicle_plate?`, `vehicle_brand?`, `insurance_company?`, `insurance_policy?` |
| `UpsertDocumentRequest` | `type`, `document_number`, `issue_date`, `expiry_date` |

---

## 6. Repositorios y Use Cases faltantes

### Estado actual

```
domain/repository/ChocRepository.kt  — solo getVehicles()
domain/usecase/GetVehiclesUseCase.kt  — solo un use case
data/repository/ChocRepositoryImpl.kt — implementación parcial
```

### Repositorios nuevos requeridos

| Repositorio (Contrato) | Métodos clave |
|---|---|
| `AuthRepository` | `register`, `login`, `socialLogin`, `logout`, `getProfile`, `updateProfile`, `forgotPassword`, `resetPassword` |
| `VehicleRepository` | `getVehicles`, `getVehicle`, `createVehicle`, `updateVehicle`, `deleteVehicle` |
| `DocumentRepository` | `getDocuments`, `upsertDocument`, `deleteDocument` |
| `IncidentRepository` | `getIncidents`, `getIncident`, `createIncident`, `updateIncident`, `deleteIncident`, `exportPdf`, `addPhotos`, `addThirdParty` |
| `MaintenanceRepository` | `getHistory`, `createRecord`, `updateRecord`, `deleteRecord` |
| `NotificationRepository` | `getNotifications`, `markAsRead`, `markAllAsRead`, `delete` |
| `SupportRepository` | `getEmergencyContacts`, `getNearbyWorkshops` |

### Use Cases nuevos requeridos (selección principal)

```
domain/usecase/auth/
  LoginUseCase.kt
  RegisterUseCase.kt
  LogoutUseCase.kt
  GetProfileUseCase.kt
  UpdateFcmTokenUseCase.kt

domain/usecase/vehicle/
  GetVehiclesUseCase.kt         ← ya existe
  CreateVehicleUseCase.kt
  GetVehicleDetailUseCase.kt

domain/usecase/document/
  GetDocumentsUseCase.kt
  UpsertDocumentUseCase.kt

domain/usecase/incident/
  CreateIncidentUseCase.kt
  GetIncidentsUseCase.kt
  GetIncidentDetailUseCase.kt
  ExportIncidentPdfUseCase.kt
  AddThirdPartyUseCase.kt

domain/usecase/maintenance/
  GetMaintenanceHistoryUseCase.kt
  CreateMaintenanceRecordUseCase.kt

domain/usecase/notification/
  GetNotificationsUseCase.kt
  MarkNotificationReadUseCase.kt

domain/usecase/support/
  GetEmergencyContactsUseCase.kt
  GetNearbyWorkshopsUseCase.kt
```

---

## 7. ViewModels faltantes

| ViewModel | Pantalla que lo consume | State principal |
|---|---|---|
| `AuthViewModel` | Login, CreateAccount | `authState`, `isLoading`, `error` |
| `VehicleViewModel` | Dashboard, Selección de vehículo | `vehicles`, `selectedVehicle` |
| `DocumentViewModel` | DocumentsScreen, DocumentDetailScreen | `documents`, `selectedDocument` |
| `MaintenanceViewModel` | MaintenanceHistoryScreen | `records`, `stats` |
| `NotificationViewModel` | NotificationsScreen | `notifications`, `unreadCount` |
| `SupportViewModel` | EmergencySupportScreen | `emergencyContacts`, `workshops` |

---

## 8. Pantallas y flujos faltantes

### 8.1 Selección de vehículo en el flujo de registro de accidente

**Problema:** `RegisterAccidentContextScreen` no pide al usuario qué vehículo estuvo involucrado. La API requiere `vehicle_id` en `POST /incidents`.

**Solución:** Agregar un paso previo `SelectVehicleScreen` o un selector desplegable en `RegisterAccidentContextScreen`:

```kotlin
// Agregar en RegisterAccidentContextScreen o como Step 0
@Composable
fun VehicleSelectorStep(
    vehicles: List<Vehicle>,
    selectedVehicleId: String,
    onSelect: (String) -> Unit
)
```

---

### 8.2 Pantalla de recuperación de contraseña

**Faltante:** El botón "¿Olvidaste tu contraseña?" en `LoginScreen` no navega a ningún lado.

Requiere dos pantallas:
- `ForgotPasswordScreen` — ingresa email → `POST /auth/password/forgot`
- `ResetPasswordScreen` — ingresa token + nueva contraseña → `POST /auth/password/reset`

---

### 8.3 Pantalla de registro de vehículo

**Faltante:** No existe pantalla para registrar un vehículo nuevo (`POST /vehicles`). El flujo de onboarding debería incluirla después de crear la cuenta.

Campos mínimos: placa, marca, modelo, año, color, tipo (AUTOMOVIL/MOTOCICLETA), foto (opcional).

---

### 8.4 Pantalla de terceros involucrados

**Faltante:** La API soporta `POST /incidents/{id}/third-parties` pero no hay pantalla para agregar este dato. Es fundamental para el reporte oficial.

```
AddThirdPartyScreen(
    incidentId: String,
    onSaved: () -> Unit
)
```

---

### 8.5 Flujo de autenticación social

**Estado actual:** Los botones Google y Apple en `LoginScreen` no hacen nada.

**Requerido:**
- Integrar Google Sign-In SDK para Android
- Integrar Sign in with Apple
- Enviar el token de OAuth a `POST /auth/social`

---

## 9. Integración FCM (notificaciones push)

**Estado actual:** No hay ninguna configuración de Firebase en el proyecto Android.

**Pasos requeridos:**

1. Agregar `google-services.json` al módulo `app/`
2. Añadir dependencia en `build.gradle.kts`:
   ```kotlin
   implementation("com.google.firebase:firebase-messaging-ktx:24.x")
   ```
3. Crear `ChocFirebaseMessagingService` que extienda `FirebaseMessagingService`:
   ```kotlin
   class ChocFirebaseMessagingService : FirebaseMessagingService() {
       override fun onNewToken(token: String) {
           // Enviar a POST /auth/profile con { "fcm_token": token }
       }
       override fun onMessageReceived(message: RemoteMessage) {
           // Mostrar notificación local según message.data["type"]
       }
   }
   ```
4. Declarar el servicio en `AndroidManifest.xml`
5. Llamar `UpdateFcmTokenUseCase` al iniciar sesión exitosamente

---

## 10. Carga de fotos — Multipart form data

**Problema crítico:** La API requiere que las fotos de incidente se envíen como `multipart/form-data`, pero `ChocAppApi.kt` no tiene ningún endpoint `@Multipart`.

**Implementación requerida en `RegisterAccidentPhotosScreen` / `IncidentViewModel`:**

```kotlin
// En IncidentRepository / IncidentService
suspend fun createIncident(report: IncidentReport): Result<Incident> {
    val fields = mapOf(
        "vehicle_id"        to report.vehicleId.toRequestBody(),
        "description"       to report.description.toRequestBody(),
        "incident_date"     to report.incidentDate.toRequestBody(),
        "incident_time"     to report.incidentTime.toRequestBody(),
        "location_address"  to report.locationAddress.toRequestBody(),
        "weather_condition" to report.weather.name.toRequestBody(),
        "road_condition"    to report.roadState.name.toRequestBody()
    )
    val photoParts = report.photoUris.mapIndexed { index, uri ->
        val bytes = context.contentResolver.openInputStream(uri)!!.readBytes()
        val body = bytes.toRequestBody("image/jpeg".toMediaType())
        MultipartBody.Part.createFormData("photos[$index]", "photo_$index.jpg", body)
    }
    return api.createIncident(fields, photoParts)
}
```

**Validaciones mínimas a implementar en el cliente:**
- Mínimo 4 fotos antes de enviar (la API retorna 422 si hay menos)
- Tamaño máximo recomendado por foto: 5 MB (comprimir antes de subir)
- Formatos aceptados: `image/jpeg`, `image/png`

---

## 11. Paginación ignorada

**Problema:** Los endpoints de incidentes y notificaciones retornan listas paginadas pero la app no maneja el campo `meta`.

**Implementación recomendada con `Pager` de Jetpack:**

```kotlin
// domain/model/PaginatedList.kt
data class PaginatedList<T>(
    val items: List<T>,
    val currentPage: Int,
    val lastPage: Int,
    val total: Int
) {
    val hasNextPage: Boolean get() = currentPage < lastPage
}
```

Los ViewModels de `MyCrashesScreen` y `NotificationsScreen` deben implementar `PagingSource` o manejar paginación manual con `loadMore()`.

---

## 12. Mapeo de ángulos de foto

**Problema:** Los hotspots en `RegisterAccidentPhotosScreen` usan etiquetas en español que no corresponden al enum de la API.

| Etiqueta en app (español) | Enum de la API | ¿Correcto? |
|---|---|---|
| "Frente" | `FRONT` | ❌ No mapeado |
| "Frontal Izq" | `FRONT_LEFT` | ❌ No mapeado |
| "Frontal Der" | `FRONT_RIGHT` | ❌ No mapeado |
| "Lado Izq" | `LEFT` | ❌ No mapeado |
| "Lado Der" | `RIGHT` | ❌ No mapeado |
| "Trasero Izq" | `REAR_LEFT` | ❌ No mapeado |
| "Trasero Der" | `REAR_RIGHT` | ❌ No mapeado |
| "Trasero" | `REAR` | ❌ No mapeado |
| — (no existe) | `INTERIOR` | ❌ Falta en la app |
| — (no existe) | `ODOMETER` | ❌ Falta en la app |
| — (no existe) | `EXTRA` | ❌ Falta en la app |

**Corrección:** Cada `Hotspot` composable debe recibir un `PhotoAngle` tipado y enviarlo al API en el campo `angle`:

```kotlin
data class HotspotConfig(
    val angle: PhotoAngle,
    val labelEs: String,      // Para mostrar en UI
    val alignment: Alignment  // Posición sobre la ilustración del auto
)

val hotspots = listOf(
    HotspotConfig(PhotoAngle.FRONT,       "Frente",       Alignment.TopCenter),
    HotspotConfig(PhotoAngle.FRONT_LEFT,  "Frontal Izq",  Alignment.TopStart),
    HotspotConfig(PhotoAngle.FRONT_RIGHT, "Frontal Der",  Alignment.TopEnd),
    HotspotConfig(PhotoAngle.LEFT,        "Lado Izq",     Alignment.CenterStart),
    HotspotConfig(PhotoAngle.RIGHT,       "Lado Der",     Alignment.CenterEnd),
    HotspotConfig(PhotoAngle.REAR_LEFT,   "Trasero Izq",  Alignment.BottomStart),
    HotspotConfig(PhotoAngle.REAR_RIGHT,  "Trasero Der",  Alignment.BottomEnd),
    HotspotConfig(PhotoAngle.REAR,        "Trasero",      Alignment.BottomCenter)
)
```

---

## 13. Corrección de URL base

**Problema:** La URL base actual es un placeholder inválido.

```kotlin
// ACTUAL — INCORRECTO
const val BASE_URL = "https://api.chocapp.com/"  // ❌ No es la URL real

// CORRECTO
// Producción
const val BASE_URL_PROD    = "https://chocapp.reddantechnology.com/api/v1/"
// Staging
const val BASE_URL_STAGING = "https://stg.chocapp.reddantechnology.com/api/v1/"
```

Usar `BuildConfig` para seleccionar el ambiente:

```kotlin
// En build.gradle.kts (módulo app)
buildTypes {
    debug {
        buildConfigField("String", "API_BASE_URL",
            "\"https://stg.chocapp.reddantechnology.com/api/v1/\"")
    }
    release {
        buildConfigField("String", "API_BASE_URL",
            "\"https://chocapp.reddantechnology.com/api/v1/\"")
    }
}

// En ChocAppApi.kt
const val BASE_URL = BuildConfig.API_BASE_URL
```

---

## 14. Prioridad de implementación

### 🔴 Prioridad ALTA — Bloquea el funcionamiento básico

| # | Tarea |
|---|---|
| 1 | Corregir `BASE_URL` con ambientes debug/release |
| 2 | Crear clase `ApiResponse<T>` y actualizar Retrofit para deserializar el sobre |
| 3 | Corregir `IncidentStatus` enum (BORRADOR/REPORTADO/EN_REVISION/FINALIZADO) |
| 4 | Crear `AuthRepository` + `LoginUseCase` + `RegisterUseCase` + `AuthViewModel` |
| 5 | Implementar `AuthInterceptor` con Bearer token |
| 6 | Corregir modelo `User` (campo `name`, agregar `fcm_token`) |
| 7 | Agregar `vehicle_id` al flujo de creación de incidente |
| 8 | Implementar upload multipart para fotos de incidente |

### 🟡 Prioridad MEDIA — Funcionalidades incompletas

| # | Tarea |
|---|---|
| 9 | Crear `DocumentRepository` + `DocumentViewModel` con llamadas reales a API |
| 10 | Crear `MaintenanceRepository` + `MaintenanceViewModel` con llamadas reales a API |
| 11 | Crear `NotificationRepository` + `NotificationViewModel` |
| 12 | Agregar campos faltantes a `Incident` (fecha, hora, coordenadas) |
| 13 | Corregir `NotificationType` enum |
| 14 | Crear `Document`, `IncidentPhoto`, `ThirdParty` como modelos de dominio |
| 15 | Mapear `PhotoAngle` enum en los hotspots de `RegisterAccidentPhotosScreen` |

### 🟢 Prioridad BAJA — Mejoras y features nuevas

| # | Tarea |
|---|---|
| 16 | Integrar FCM (`ChocFirebaseMessagingService`) |
| 17 | Pantalla de registro de vehículo |
| 18 | Pantalla de recuperación de contraseña |
| 19 | Pantalla de terceros involucrados |
| 20 | Autenticación social (Google Sign-In) |
| 21 | Implementar paginación en MyCrashesScreen y NotificationsScreen |
| 22 | Consumir `GET /support/emergency-contacts` en `EmergencySupportScreen` |
| 23 | Consumir `GET /support/workshops` con ubicación GPS |

---

*Documento generado con Claude Code — ChocApp v1.0 · Redd An Technology © 2026*
