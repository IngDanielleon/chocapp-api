# ChocApp — Guía de Integración Android/Kotlin

> **Versión API:** 1.0.0  
> **Base URL Producción:** `https://chocapp.reddantechnology.com/api/v1/`  
> **Base URL Staging:** `https://stg.chocapp.reddantechnology.com/api/v1/`  
> **Autenticación:** Bearer Token (Laravel Sanctum — 90 días)  
> **Formato:** JSON / multipart/form-data para archivos  

---

## Tabla de Contenidos

1. [Configuración del proyecto](#1-configuración-del-proyecto)
2. [Capa de red — RetrofitClient](#2-capa-de-red--retrofitclient)
3. [Wrapper de respuesta ApiResponse](#3-wrapper-de-respuesta-apiresponse)
4. [Modelos de dominio](#4-modelos-de-dominio)
5. [DTOs de solicitud](#5-dtos-de-solicitud)
6. [Interfaz Retrofit — ChocAppApi](#6-interfaz-retrofit--chocappapi)
7. [Repositorios](#7-repositorios)
8. [Use Cases](#8-use-cases)
9. [Manejo de errores](#9-manejo-de-errores)
10. [Carga de fotos — Multipart](#10-carga-de-fotos--multipart)
11. [Paginación](#11-paginación)
12. [Notificaciones push — FCM](#12-notificaciones-push--fcm)
13. [Inyección de dependencias — Hilt](#13-inyección-de-dependencias--hilt)
14. [Mapeo de ángulos de foto](#14-mapeo-de-ángulos-de-foto)
15. [Ambientes — BuildConfig](#15-ambientes--buildconfig)
16. [Flujos críticos paso a paso](#16-flujos-críticos-paso-a-paso)

---

## 1. Configuración del proyecto

### `build.gradle.kts` (módulo `app`)

```kotlin
android {
    buildFeatures {
        buildConfig = true
    }
    buildTypes {
        debug {
            buildConfigField(
                "String", "API_BASE_URL",
                "\"https://stg.chocapp.reddantechnology.com/api/v1/\""
            )
        }
        release {
            isMinifyEnabled = true
            buildConfigField(
                "String", "API_BASE_URL",
                "\"https://chocapp.reddantechnology.com/api/v1/\""
            )
        }
    }
}

dependencies {
    // Retrofit + OkHttp
    implementation("com.squareup.retrofit2:retrofit:2.11.0")
    implementation("com.squareup.retrofit2:converter-gson:2.11.0")
    implementation("com.squareup.okhttp3:okhttp:4.12.0")
    implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")

    // Coroutines
    implementation("org.jetbrains.kotlinx:kotlinx-coroutines-android:1.8.1")

    // Hilt
    implementation("com.google.dagger:hilt-android:2.51.1")
    kapt("com.google.dagger:hilt-compiler:2.51.1")

    // DataStore (para guardar el token)
    implementation("androidx.datastore:datastore-preferences:1.1.1")

    // Firebase Messaging
    implementation(platform("com.google.firebase:firebase-bom:33.0.0"))
    implementation("com.google.firebase:firebase-messaging-ktx")

    // Coil (carga de imágenes desde S3)
    implementation("io.coil-kt:coil-compose:2.6.0")

    // Paging 3
    implementation("androidx.paging:paging-runtime-ktx:3.3.0")
    implementation("androidx.paging:paging-compose:3.3.0")
}
```

---

## 2. Capa de red — RetrofitClient

### `data/remote/interceptor/AuthInterceptor.kt`

```kotlin
class AuthInterceptor(
    private val tokenProvider: suspend () -> String?
) : Interceptor {

    override fun intercept(chain: Interceptor.Chain): Response {
        val token = runBlocking { tokenProvider() }
        val request = chain.request().newBuilder().apply {
            token?.let { addHeader("Authorization", "Bearer $it") }
            addHeader("Accept", "application/json")
        }.build()
        return chain.proceed(request)
    }
}
```

### `data/remote/RetrofitClient.kt`

```kotlin
object RetrofitClient {

    fun create(tokenProvider: suspend () -> String?): ChocAppApi {
        val loggingInterceptor = HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG)
                HttpLoggingInterceptor.Level.BODY
            else
                HttpLoggingInterceptor.Level.NONE
        }

        val client = OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor(tokenProvider))
            .addInterceptor(loggingInterceptor)
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(60, TimeUnit.SECONDS)
            .writeTimeout(120, TimeUnit.SECONDS)   // Subida de fotos
            .build()

        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(client)
            .addConverterFactory(GsonConverterFactory.create(buildGson()))
            .build()
            .create(ChocAppApi::class.java)
    }

    private fun buildGson(): Gson = GsonBuilder()
        .setFieldNamingPolicy(FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES)
        .create()
}
```

> **Nota:** La API usa `snake_case` en todos los campos JSON. El `FieldNamingPolicy.LOWER_CASE_WITH_UNDERSCORES` convierte automáticamente `camelCase` de Kotlin a `snake_case` de la API y viceversa.

---

## 3. Wrapper de respuesta ApiResponse

Todos los endpoints (excepto `export-pdf`) devuelven este sobre JSON:

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { ... },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}
```

### `data/remote/dto/ApiResponse.kt`

```kotlin
data class ApiResponse<T>(
    val success: Boolean,
    val message: String,
    @SerializedName("data") val data: T? = null,
    val errors: Map<String, List<String>>? = null,
    val meta: PaginationMeta? = null
)

data class PaginationMeta(
    val currentPage: Int,
    val lastPage: Int,
    val perPage: Int,
    val total: Int
)

// Resultado seguro para repositorios
sealed class Resource<out T> {
    data class Success<T>(val data: T) : Resource<T>()
    data class Error(
        val message: String,
        val errors: Map<String, List<String>>? = null,
        val code: Int = 0
    ) : Resource<Nothing>()
    object Loading : Resource<Nothing>()
}
```

### Extensión para convertir `ApiResponse` a `Resource`

```kotlin
// data/remote/dto/ApiResponse.kt
suspend fun <T> safeApiCall(call: suspend () -> ApiResponse<T>): Resource<T> {
    return try {
        val response = call()
        if (response.success && response.data != null) {
            Resource.Success(response.data)
        } else {
            Resource.Error(response.message, response.errors)
        }
    } catch (e: HttpException) {
        val body = e.response()?.errorBody()?.string()
        val errorResponse = try {
            Gson().fromJson(body, ApiResponse::class.java)
        } catch (_: Exception) { null }
        Resource.Error(
            message = errorResponse?.message ?: "Error del servidor (${e.code()})",
            code = e.code()
        )
    } catch (e: IOException) {
        Resource.Error("Sin conexión a internet. Verifica tu red.")
    } catch (e: Exception) {
        Resource.Error("Error inesperado: ${e.localizedMessage}")
    }
}
```

---

## 4. Modelos de dominio

### `domain/model/User.kt`

```kotlin
data class User(
    val id: String,
    val name: String,               // La API usa UN solo campo "name"
    val email: String,
    val phoneNumber: String,
    val idType: IdType,
    val idNumber: String,
    val profilePicUrl: String? = null,
    val termsAccepted: Boolean = false,
    val vehiclesCount: Int = 0
)

enum class IdType { CC, CE, PPT, PASAPORTE }

data class AuthData(
    val user: User,
    val token: String
)
```

### `domain/model/Vehicle.kt`

```kotlin
data class Vehicle(
    val id: String,
    val userId: String,
    val plate: String,
    val brand: String,
    val model: String,
    val year: Int,
    val color: String,
    val type: VehicleType,
    val photoUrl: String? = null,
    val documents: List<Document> = emptyList()
)

enum class VehicleType { MOTOCICLETA, AUTOMOVIL }
```

### `domain/model/Document.kt`

```kotlin
data class Document(
    val id: String,
    val type: DocumentType,
    val documentNumber: String,
    val issueDate: String?,         // "YYYY-MM-DD"
    val expiryDate: String,         // "YYYY-MM-DD"
    val status: DocumentStatus,     // Calculado por la API (accessor)
    val daysRemaining: Int,
    val hasPdf: Boolean = false,
    val notes: String? = null
)

enum class DocumentType   { SOAT, TECNOMECANICA, LICENCIA }
enum class DocumentStatus { VIGENTE, VENCE_PRONTO, VENCIDO }
```

### `domain/model/Incident.kt`

```kotlin
data class Incident(
    val id: String,
    val vehicleId: String,
    val title: String,
    val description: String,
    val incidentDate: String,           // "YYYY-MM-DD"
    val incidentTime: String,           // "HH:mm"
    val locationAddress: String,
    val latitude: Double,
    val longitude: Double,
    val weatherCondition: WeatherCondition,
    val roadCondition: RoadCondition,
    val policeReportNumber: String? = null,
    val status: IncidentStatus,
    val statusLabel: String,
    val coverPhotoUrl: String? = null,
    val photos: List<IncidentPhoto> = emptyList(),
    val thirdParties: List<ThirdParty> = emptyList(),
    val reportPdfUrl: String? = null,
    val hasPdf: Boolean = false,
    val vehicle: Vehicle? = null
)

// ✅ Valores EXACTOS de la API — no usar PENDING/IN_PROGRESS/RESOLVED
enum class IncidentStatus { BORRADOR, REPORTADO, EN_REVISION, FINALIZADO }
enum class WeatherCondition { SOLEADO, LLUVIOSO, NUBLADO, NOCHE }
enum class RoadCondition    { BUEN_ESTADO, HUMEDO, HUECOS, DERRUMBE }
```

### `domain/model/IncidentPhoto.kt`

```kotlin
data class IncidentPhoto(
    val id: String,
    val angle: PhotoAngle,
    val imageUrl: String,
    val takenAt: String? = null
)

enum class PhotoAngle {
    FRONT, FRONT_RIGHT, RIGHT, REAR_RIGHT,
    REAR,  REAR_LEFT,   LEFT,  FRONT_LEFT,
    INTERIOR, ODOMETER, EXTRA
}
```

### `domain/model/ThirdParty.kt`

```kotlin
data class ThirdParty(
    val id: String,
    val partyType: PartyType,
    val plate: String? = null,
    val brand: String? = null,
    val model: String? = null,
    val color: String? = null,
    val driverName: String? = null,
    val driverId: String? = null,
    val driverPhone: String? = null,
    val insuranceCompany: String? = null,
    val insurancePolicy: String? = null
)

enum class PartyType { VEHICULO, PEATON, CICLISTA }
```

### `domain/model/MaintenanceRecord.kt`

```kotlin
data class MaintenanceRecord(
    val id: String,
    val maintenanceDate: String,    // "YYYY-MM-DD"
    val type: MaintenanceType,
    val cost: Double? = null,       // En pesos COP
    val workshopName: String? = null,
    val currentMileage: Int? = null,
    val notes: String? = null,
    val nextDate: String? = null,   // "YYYY-MM-DD"
    val nextMileage: Int? = null
)

enum class MaintenanceType {
    ACEITE, FRENOS, LLANTAS, BATERIA,
    FILTROS, SUSPENSION, REVISION_GENERAL, OTRO
}
```

### `domain/model/Notification.kt`

```kotlin
data class AppNotification(
    val id: String,
    val title: String,
    val body: String,
    val type: NotificationType,
    val data: Map<String, Any>? = null,
    val isRead: Boolean,
    val readAt: String? = null,
    val createdAt: String
)

// ✅ Valores EXACTOS de la API
enum class NotificationType {
    INCIDENT_UPDATE,
    DOCUMENT_EXPIRING,
    MAINTENANCE_REMINDER,
    ALERT,
    INFO
    // SUCCESS no existe en la API
}
```

### `domain/model/Support.kt`

```kotlin
data class EmergencyContact(
    val name: String,
    val phone: String,
    val type: String    // "EMERGENCY" | "POLICE" | "AMBULANCE" | "FIRE" | "TRANSIT" | "TOW_TRUCK" | "LEGAL"
)

data class Workshop(
    val name: String,
    val address: String,
    val phone: String,
    val latitude: Double,
    val longitude: Double,
    val distanceKm: Double? = null
)
```

---

## 5. DTOs de solicitud

### `data/remote/dto/request/AuthRequests.kt`

```kotlin
data class LoginRequest(
    val email: String,
    val password: String
)

data class SocialLoginRequest(
    val provider: String,   // "google" | "apple" | "facebook"
    val token: String
)

data class ForgotPasswordRequest(val email: String)

data class ResetPasswordRequest(
    val token: String,
    val email: String,
    val password: String,
    val passwordConfirmation: String
)
```

### `data/remote/dto/request/IncidentRequests.kt`

```kotlin
data class UpdateIncidentRequest(
    val title: String? = null,
    val description: String? = null,
    val status: String? = null,            // "BORRADOR"|"REPORTADO"|"EN_REVISION"|"FINALIZADO"
    val policeReportNumber: String? = null
)

data class CreateThirdPartyRequest(
    val partyType: String,                 // "VEHICULO"|"PEATON"|"CICLISTA"
    val plate: String? = null,
    val brand: String? = null,
    val model: String? = null,
    val color: String? = null,
    val driverName: String? = null,        // ⚠️ API usa driver_name (snake_case automático)
    val driverId: String? = null,
    val driverPhone: String? = null,
    val insuranceCompany: String? = null,
    val insurancePolicy: String? = null
)
```

### `data/remote/dto/request/MaintenanceRequests.kt`

```kotlin
data class CreateMaintenanceRequest(
    val maintenanceDate: String,           // ⚠️ Campo: maintenance_date (no "date")
    val type: String,
    val cost: Double? = null,
    val workshopName: String? = null,
    val currentMileage: Int? = null,       // ⚠️ Campo: current_mileage (no "mileage")
    val notes: String? = null,
    val nextDate: String? = null,
    val nextMileage: Int? = null
)
```

### `data/remote/dto/request/DocumentRequests.kt`

```kotlin
data class UpsertDocumentFields(
    val type: String,                      // "SOAT"|"TECNOMECANICA"|"LICENCIA"
    val documentNumber: String,
    val issueDate: String? = null,
    val expiryDate: String,
    val notes: String? = null
    // pdf_file se envía por separado como MultipartBody.Part
)
```

---

## 6. Interfaz Retrofit — ChocAppApi

### `data/remote/ChocAppApi.kt`

```kotlin
interface ChocAppApi {

    // ════════════════════════════════════════════════════════
    // AUTH
    // ════════════════════════════════════════════════════════

    @Multipart
    @POST("auth/register")
    suspend fun register(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part profilePic: MultipartBody.Part? = null
    ): ApiResponse<AuthData>

    @POST("auth/login")
    suspend fun login(@Body body: LoginRequest): ApiResponse<AuthData>

    @POST("auth/social")
    suspend fun socialLogin(@Body body: SocialLoginRequest): ApiResponse<AuthData>

    @POST("auth/logout")
    suspend fun logout(): ApiResponse<Unit>

    @GET("auth/me")
    suspend fun getProfile(): ApiResponse<User>

    @Multipart
    @PUT("auth/profile")
    suspend fun updateProfile(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part profilePic: MultipartBody.Part? = null
    ): ApiResponse<User>

    @POST("auth/password/forgot")
    suspend fun forgotPassword(@Body body: ForgotPasswordRequest): ApiResponse<Unit>

    @POST("auth/password/reset")
    suspend fun resetPassword(@Body body: ResetPasswordRequest): ApiResponse<Unit>

    // ════════════════════════════════════════════════════════
    // VEHÍCULOS
    // ════════════════════════════════════════════════════════

    @GET("vehicles")
    suspend fun getVehicles(): ApiResponse<List<Vehicle>>

    @Multipart
    @POST("vehicles")
    suspend fun createVehicle(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part photo: MultipartBody.Part? = null
    ): ApiResponse<Vehicle>

    @GET("vehicles/{id}")
    suspend fun getVehicleById(@Path("id") id: String): ApiResponse<Vehicle>

    @Multipart
    @PUT("vehicles/{id}")
    suspend fun updateVehicle(
        @Path("id") id: String,
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part photo: MultipartBody.Part? = null
    ): ApiResponse<Vehicle>

    @DELETE("vehicles/{id}")
    suspend fun deleteVehicle(@Path("id") id: String): ApiResponse<Unit>

    // ════════════════════════════════════════════════════════
    // DOCUMENTOS
    // ════════════════════════════════════════════════════════

    @GET("vehicles/{vehicleId}/documents")
    suspend fun getDocuments(
        @Path("vehicleId") vehicleId: String
    ): ApiResponse<List<Document>>

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

    // ════════════════════════════════════════════════════════
    // INCIDENTES
    // ════════════════════════════════════════════════════════

    @GET("incidents")
    suspend fun getIncidents(
        @Query("status") status: String? = null,
        @Query("from") from: String? = null,
        @Query("to") to: String? = null,
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 15
    ): ApiResponse<List<Incident>>

    @Multipart
    @POST("incidents")
    suspend fun createIncident(
        @PartMap fields: Map<String, @JvmSuppressWildcards RequestBody>,
        @Part photos: List<MultipartBody.Part>   // Mínimo 4 obligatorio
    ): ApiResponse<Incident>

    @GET("incidents/{id}")
    suspend fun getIncidentById(@Path("id") id: String): ApiResponse<Incident>

    @PUT("incidents/{id}")
    suspend fun updateIncident(
        @Path("id") id: String,
        @Body body: UpdateIncidentRequest
    ): ApiResponse<Incident>

    @DELETE("incidents/{id}")
    suspend fun deleteIncident(@Path("id") id: String): ApiResponse<Unit>

    // Retorna stream binario — NO envuelto en ApiResponse
    @Streaming
    @GET("incidents/{id}/export-pdf")
    suspend fun exportIncidentPdf(@Path("id") id: String): ResponseBody

    @Multipart
    @POST("incidents/{id}/photos")
    suspend fun addPhotos(
        @Path("id") id: String,
        @Part photos: List<MultipartBody.Part>
    ): ApiResponse<Unit>

    @DELETE("incidents/{id}/photos/{photoId}")
    suspend fun deletePhoto(
        @Path("id") id: String,
        @Path("photoId") photoId: String
    ): ApiResponse<Unit>

    @POST("incidents/{id}/third-parties")
    suspend fun addThirdParty(
        @Path("id") id: String,
        @Body body: CreateThirdPartyRequest
    ): ApiResponse<ThirdParty>

    // ════════════════════════════════════════════════════════
    // MANTENIMIENTO
    // ════════════════════════════════════════════════════════

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

    // ════════════════════════════════════════════════════════
    // NOTIFICACIONES
    // ════════════════════════════════════════════════════════

    @GET("notifications")
    suspend fun getNotifications(
        @Query("unread_only") unreadOnly: Boolean = false,
        @Query("page") page: Int = 1,
        @Query("per_page") perPage: Int = 20
    ): ApiResponse<List<AppNotification>>

    @PATCH("notifications/read-all")
    suspend fun markAllAsRead(): ApiResponse<Unit>

    @PATCH("notifications/{id}/read")
    suspend fun markAsRead(@Path("id") id: String): ApiResponse<Unit>

    @DELETE("notifications/{id}")
    suspend fun deleteNotification(@Path("id") id: String): ApiResponse<Unit>

    // ════════════════════════════════════════════════════════
    // SOPORTE
    // ════════════════════════════════════════════════════════

    @GET("support/emergency-contacts")
    suspend fun getEmergencyContacts(): ApiResponse<List<EmergencyContact>>

    @GET("support/workshops")
    suspend fun getNearbyWorkshops(
        @Query("latitude") latitude: Double,
        @Query("longitude") longitude: Double,
        @Query("radius_km") radiusKm: Int = 10
    ): ApiResponse<List<Workshop>>
}
```

---

## 7. Repositorios

### `domain/repository/AuthRepository.kt` (contrato)

```kotlin
interface AuthRepository {
    suspend fun register(
        name: String, email: String, password: String,
        idType: String, idNumber: String, phoneNumber: String,
        profilePicUri: Uri? = null
    ): Resource<AuthData>
    suspend fun login(email: String, password: String): Resource<AuthData>
    suspend fun socialLogin(provider: String, token: String): Resource<AuthData>
    suspend fun logout(): Resource<Unit>
    suspend fun getProfile(): Resource<User>
    suspend fun updateFcmToken(token: String): Resource<User>
    suspend fun forgotPassword(email: String): Resource<Unit>
    suspend fun resetPassword(token: String, email: String, password: String): Resource<Unit>
}
```

### `data/repository/AuthRepositoryImpl.kt`

```kotlin
class AuthRepositoryImpl @Inject constructor(
    private val api: ChocAppApi,
    private val tokenStore: TokenStore,
    private val context: Context
) : AuthRepository {

    override suspend fun register(
        name: String, email: String, password: String,
        idType: String, idNumber: String, phoneNumber: String,
        profilePicUri: Uri?
    ): Resource<AuthData> = safeApiCall {
        val fields = mutableMapOf(
            "name"                  to name.toRequestBody(),
            "email"                 to email.toRequestBody(),
            "password"              to password.toRequestBody(),
            "password_confirmation" to password.toRequestBody(),
            "id_type"               to idType.toRequestBody(),
            "id_number"             to idNumber.toRequestBody(),
            "phone_number"          to phoneNumber.toRequestBody(),
            "terms_accepted"        to "1".toRequestBody()
        )
        val picPart = profilePicUri?.toMultipartPart(context, "profile_pic")
        api.register(fields, picPart)
    }.also { if (it is Resource.Success) tokenStore.save(it.data.token) }

    override suspend fun login(email: String, password: String): Resource<AuthData> =
        safeApiCall { api.login(LoginRequest(email, password)) }
            .also { if (it is Resource.Success) tokenStore.save(it.data.token) }

    override suspend fun logout(): Resource<Unit> =
        safeApiCall { api.logout() }
            .also { tokenStore.clear() }

    override suspend fun updateFcmToken(token: String): Resource<User> = safeApiCall {
        val fields = mapOf("fcm_token" to token.toRequestBody())
        api.updateProfile(fields, null)
    }

    // ... resto de implementaciones
}
```

### `domain/repository/IncidentRepository.kt` (contrato)

```kotlin
interface IncidentRepository {
    suspend fun getIncidents(
        status: String? = null,
        from: String? = null,
        to: String? = null,
        page: Int = 1
    ): Resource<List<Incident>>
    suspend fun getIncident(id: String): Resource<Incident>
    suspend fun createIncident(report: IncidentDraft): Resource<Incident>
    suspend fun updateIncident(id: String, request: UpdateIncidentRequest): Resource<Incident>
    suspend fun deleteIncident(id: String): Resource<Unit>
    suspend fun exportPdf(id: String): Resource<ResponseBody>
    suspend fun addThirdParty(incidentId: String, request: CreateThirdPartyRequest): Resource<ThirdParty>
}
```

---

## 8. Use Cases

### `domain/usecase/auth/LoginUseCase.kt`

```kotlin
class LoginUseCase @Inject constructor(
    private val authRepository: AuthRepository,
    private val updateFcmTokenUseCase: UpdateFcmTokenUseCase
) {
    suspend operator fun invoke(email: String, password: String): Resource<AuthData> {
        val result = authRepository.login(email, password)
        if (result is Resource.Success) {
            // Registrar el FCM token en el servidor tras login exitoso
            FirebaseMessaging.getInstance().token.addOnSuccessListener { fcmToken ->
                // Lanzar en un scope aparte para no bloquear
                updateFcmTokenUseCase.launchSilently(fcmToken)
            }
        }
        return result
    }
}
```

### `domain/usecase/incident/CreateIncidentUseCase.kt`

```kotlin
class CreateIncidentUseCase @Inject constructor(
    private val incidentRepository: IncidentRepository
) {
    suspend operator fun invoke(draft: IncidentDraft): Resource<Incident> {
        // Validación previa al envío (evitar llamada innecesaria a la API)
        require(draft.vehicleId.isNotBlank()) { "Selecciona un vehículo" }
        require(draft.description.length >= 10) { "La descripción debe tener mínimo 10 caracteres" }
        require(draft.photoUris.size >= 4) { "Se requieren mínimo 4 fotos" }
        require(draft.latitude != null && draft.longitude != null) { "Se requiere ubicación GPS" }

        return incidentRepository.createIncident(draft)
    }
}
```

### `domain/usecase/auth/UpdateFcmTokenUseCase.kt`

```kotlin
class UpdateFcmTokenUseCase @Inject constructor(
    private val authRepository: AuthRepository,
    private val scope: CoroutineScope     // ApplicationScope
) {
    suspend operator fun invoke(token: String): Resource<User> =
        authRepository.updateFcmToken(token)

    fun launchSilently(token: String) {
        scope.launch {
            runCatching { invoke(token) }
        }
    }
}
```

---

## 9. Manejo de errores

### Códigos de error de la API

| Código HTTP | Significado | Acción recomendada |
|---|---|---|
| `401` | Token expirado o inválido | Redirigir a Login, borrar token |
| `403` | No tienes permiso sobre este recurso | Mostrar mensaje, no reintentar |
| `404` | Recurso no encontrado | Mostrar estado vacío |
| `422` | Error de validación | Mostrar `errors` en el formulario |
| `429` | Rate limit superado | Mostrar "Espera antes de intentarlo de nuevo" |
| `5xx` | Error del servidor | Mostrar error genérico + botón reintentar |

### Respuesta 422 con errores de campo

```json
{
  "success": false,
  "message": "Error de validación.",
  "errors": {
    "email": ["El correo ya está en uso."],
    "photos": ["Se requieren mínimo 4 fotografías."]
  },
  "code": 422
}
```

```kotlin
// En el ViewModel — mostrar errores por campo
when (val result = loginUseCase(email, password)) {
    is Resource.Success -> { /* navegar */ }
    is Resource.Error -> {
        val fieldError = result.errors?.get("email")?.firstOrNull()
        _uiState.update { it.copy(emailError = fieldError, generalError = result.message) }
    }
    is Resource.Loading -> { /* spinner */ }
}
```

### Interceptor de sesión expirada

```kotlin
class SessionExpiredInterceptor(
    private val onSessionExpired: () -> Unit
) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val response = chain.proceed(chain.request())
        if (response.code == 401) {
            onSessionExpired()
        }
        return response
    }
}
```

---

## 10. Carga de fotos — Multipart

### Extensiones de utilidad

```kotlin
// utils/MultipartExtensions.kt

fun String.toRequestBody(): RequestBody =
    this.toRequestBody("text/plain".toMediaType())

fun Uri.toMultipartPart(context: Context, fieldName: String): MultipartBody.Part {
    val bytes = context.contentResolver.openInputStream(this)!!.use { it.readBytes() }
    val compressed = compressImage(bytes)   // Ver función abajo
    val body = compressed.toRequestBody("image/jpeg".toMediaType())
    return MultipartBody.Part.createFormData(fieldName, "${fieldName}_${System.currentTimeMillis()}.jpg", body)
}

fun compressImage(bytes: ByteArray, maxSizeKb: Int = 800): ByteArray {
    var bitmap = BitmapFactory.decodeByteArray(bytes, 0, bytes.size)
    val stream = ByteArrayOutputStream()
    var quality = 90
    bitmap.compress(Bitmap.CompressFormat.JPEG, quality, stream)
    while (stream.size() / 1024 > maxSizeKb && quality > 30) {
        stream.reset()
        quality -= 10
        bitmap.compress(Bitmap.CompressFormat.JPEG, quality, stream)
    }
    return stream.toByteArray()
}
```

### Construir las partes para `createIncident`

```kotlin
// En IncidentRepositoryImpl
suspend fun createIncident(draft: IncidentDraft): Resource<Incident> = safeApiCall {

    val fields = buildMap<String, RequestBody> {
        put("vehicle_id",        draft.vehicleId.toRequestBody())
        put("description",       draft.description.toRequestBody())
        put("incident_date",     draft.incidentDate.toRequestBody())   // "2026-05-30"
        put("incident_time",     draft.incidentTime.toRequestBody())   // "14:30"
        put("location_address",  draft.locationAddress.toRequestBody())
        put("latitude",          draft.latitude.toString().toRequestBody())
        put("longitude",         draft.longitude.toString().toRequestBody())
        put("weather_condition", draft.weatherCondition.name.toRequestBody())
        put("road_condition",    draft.roadCondition.name.toRequestBody())
        draft.title?.let          { put("title", it.toRequestBody()) }
        draft.policeReportNumber?.let { put("police_report_number", it.toRequestBody()) }
    }

    // Fotos: photos[0][file], photos[0][angle], photos[1][file], photos[1][angle]...
    val photoParts = draft.photoUris.mapIndexed { index, (uri, angle) ->
        listOf(
            uri.toMultipartPart(context, "photos[$index][file]"),
            MultipartBody.Part.createFormData("photos[$index][angle]", angle.name)
        )
    }.flatten()

    api.createIncident(fields, photoParts)
}
```

> **Importante:** Comprimir imágenes antes de subir. La API acepta hasta 10 MB por foto, pero el ancho de banda móvil recomienda ≤ 1 MB.

---

## 11. Paginación

### Respuesta paginada de la API

```json
{
  "success": true,
  "data": [ { "id": "...", ... } ],
  "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 72 }
}
```

### `IncidentPagingSource.kt`

```kotlin
class IncidentPagingSource(
    private val api: ChocAppApi,
    private val statusFilter: String? = null
) : PagingSource<Int, Incident>() {

    override fun getRefreshKey(state: PagingState<Int, Incident>): Int? =
        state.anchorPosition?.let { anchor ->
            state.closestPageToPosition(anchor)?.prevKey?.plus(1)
                ?: state.closestPageToPosition(anchor)?.nextKey?.minus(1)
        }

    override suspend fun load(params: LoadParams<Int>): LoadResult<Int, Incident> {
        val page = params.key ?: 1
        return try {
            val response = api.getIncidents(status = statusFilter, page = page)
            if (response.success && response.data != null) {
                val meta = response.meta
                LoadResult.Page(
                    data     = response.data,
                    prevKey  = if (page == 1) null else page - 1,
                    nextKey  = if (meta == null || page >= meta.lastPage) null else page + 1
                )
            } else {
                LoadResult.Error(Exception(response.message))
            }
        } catch (e: Exception) {
            LoadResult.Error(e)
        }
    }
}
```

### En el ViewModel

```kotlin
val incidents: Flow<PagingData<Incident>> = Pager(
    config = PagingConfig(pageSize = 15, enablePlaceholders = false),
    pagingSourceFactory = { IncidentPagingSource(api, statusFilter) }
).flow.cachedIn(viewModelScope)
```

---

## 12. Notificaciones push — FCM

### `AndroidManifest.xml`

```xml
<service
    android:name=".service.ChocFirebaseMessagingService"
    android:exported="false">
    <intent-filter>
        <action android:name="com.google.firebase.MESSAGING_EVENT" />
    </intent-filter>
</service>
```

### `service/ChocFirebaseMessagingService.kt`

```kotlin
@AndroidEntryPoint
class ChocFirebaseMessagingService : FirebaseMessagingService() {

    @Inject lateinit var updateFcmTokenUseCase: UpdateFcmTokenUseCase

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        updateFcmTokenUseCase.launchSilently(token)
    }

    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)
        val type = message.data["type"] ?: "INFO"
        val title = message.notification?.title ?: message.data["title"] ?: "ChocApp"
        val body  = message.notification?.body  ?: message.data["body"]  ?: ""

        showLocalNotification(type, title, body, message.data)
    }

    private fun showLocalNotification(
        type: String, title: String, body: String, data: Map<String, String>
    ) {
        val channelId = when (type) {
            "DOCUMENT_EXPIRING"    -> "channel_documents"
            "MAINTENANCE_REMINDER" -> "channel_maintenance"
            "INCIDENT_UPDATE"      -> "channel_incidents"
            else                   -> "channel_general"
        }

        val intent = when (type) {
            "INCIDENT_UPDATE" -> Intent(this, MainActivity::class.java).apply {
                putExtra("navigate_to", "incident_detail")
                putExtra("incident_id", data["incident_id"])
            }
            else -> Intent(this, MainActivity::class.java)
        }

        val notification = NotificationCompat.Builder(this, channelId)
            .setSmallIcon(R.drawable.ic_notification)
            .setContentTitle(title)
            .setContentText(body)
            .setAutoCancel(true)
            .setContentIntent(
                PendingIntent.getActivity(this, 0, intent,
                    PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE)
            )
            .build()

        NotificationManagerCompat.from(this)
            .notify(System.currentTimeMillis().toInt(), notification)
    }
}
```

### Canales de notificación (crear en `Application.onCreate`)

```kotlin
fun createNotificationChannels(context: Context) {
    val channels = listOf(
        NotificationChannelCompat.Builder("channel_documents", NotificationManagerCompat.IMPORTANCE_HIGH)
            .setName("Documentos")
            .setDescription("Alertas de vencimiento de SOAT, Tecnomecánica y Licencia")
            .build(),
        NotificationChannelCompat.Builder("channel_incidents", NotificationManagerCompat.IMPORTANCE_HIGH)
            .setName("Accidentes")
            .setDescription("Actualizaciones sobre reportes de accidentes")
            .build(),
        NotificationChannelCompat.Builder("channel_maintenance", NotificationManagerCompat.IMPORTANCE_DEFAULT)
            .setName("Mantenimiento")
            .setDescription("Recordatorios de mantenimiento vehicular")
            .build(),
        NotificationChannelCompat.Builder("channel_general", NotificationManagerCompat.IMPORTANCE_DEFAULT)
            .setName("General")
            .build()
    )
    NotificationManagerCompat.from(context).createNotificationChannelsCompat(channels)
}
```

---

## 13. Inyección de dependencias — Hilt

### `di/NetworkModule.kt`

```kotlin
@Module
@InstallIn(SingletonComponent::class)
object NetworkModule {

    @Provides @Singleton
    fun provideTokenStore(@ApplicationContext context: Context): TokenStore =
        TokenStoreImpl(context)

    @Provides @Singleton
    fun provideChocAppApi(tokenStore: TokenStore): ChocAppApi =
        RetrofitClient.create { tokenStore.getToken() }

    @Provides @Singleton
    fun provideAuthRepository(
        api: ChocAppApi,
        tokenStore: TokenStore,
        @ApplicationContext context: Context
    ): AuthRepository = AuthRepositoryImpl(api, tokenStore, context)

    @Provides @Singleton
    fun provideVehicleRepository(api: ChocAppApi): VehicleRepository =
        VehicleRepositoryImpl(api)

    @Provides @Singleton
    fun provideIncidentRepository(
        api: ChocAppApi,
        @ApplicationContext context: Context
    ): IncidentRepository = IncidentRepositoryImpl(api, context)

    @Provides @Singleton
    fun provideDocumentRepository(
        api: ChocAppApi,
        @ApplicationContext context: Context
    ): DocumentRepository = DocumentRepositoryImpl(api, context)

    @Provides @Singleton
    fun provideNotificationRepository(api: ChocAppApi): NotificationRepository =
        NotificationRepositoryImpl(api)

    @Provides @Singleton
    fun provideSupportRepository(api: ChocAppApi): SupportRepository =
        SupportRepositoryImpl(api)

    @Provides @Singleton
    @ApplicationScope
    fun provideApplicationScope(): CoroutineScope =
        CoroutineScope(SupervisorJob() + Dispatchers.Default)
}
```

### `data/local/TokenStore.kt`

```kotlin
interface TokenStore {
    suspend fun save(token: String)
    suspend fun getToken(): String?
    suspend fun clear()
}

class TokenStoreImpl(context: Context) : TokenStore {
    private val dataStore = context.createDataStore("chocapp_prefs")
    private val KEY = stringPreferencesKey("auth_token")

    override suspend fun save(token: String) {
        dataStore.edit { it[KEY] = token }
    }

    override suspend fun getToken(): String? =
        dataStore.data.catch { emit(emptyPreferences()) }
            .map { it[KEY] }.firstOrNull()

    override suspend fun clear() {
        dataStore.edit { it.remove(KEY) }
    }
}
```

---

## 14. Mapeo de ángulos de foto

La UI muestra etiquetas en español pero la API espera el enum inglés exacto.

```kotlin
// domain/model/PhotoHotspot.kt
data class PhotoHotspot(
    val angle: PhotoAngle,
    val labelEs: String,
    val isRequired: Boolean = true
)

val PHOTO_HOTSPOTS = listOf(
    PhotoHotspot(PhotoAngle.FRONT,       "Frente",        isRequired = true),
    PhotoHotspot(PhotoAngle.FRONT_LEFT,  "Frontal Izq",   isRequired = true),
    PhotoHotspot(PhotoAngle.FRONT_RIGHT, "Frontal Der",   isRequired = true),
    PhotoHotspot(PhotoAngle.LEFT,        "Lateral Izq",   isRequired = false),
    PhotoHotspot(PhotoAngle.RIGHT,       "Lateral Der",   isRequired = false),
    PhotoHotspot(PhotoAngle.REAR_LEFT,   "Trasero Izq",   isRequired = false),
    PhotoHotspot(PhotoAngle.REAR_RIGHT,  "Trasero Der",   isRequired = false),
    PhotoHotspot(PhotoAngle.REAR,        "Trasero",       isRequired = true),
    PhotoHotspot(PhotoAngle.INTERIOR,    "Interior",      isRequired = false),
    PhotoHotspot(PhotoAngle.ODOMETER,    "Odómetro",      isRequired = false),
    PhotoHotspot(PhotoAngle.EXTRA,       "Extra",         isRequired = false)
)

// Cuando el usuario toma/selecciona la foto:
data class PhotoSelection(
    val uri: Uri,
    val angle: PhotoAngle      // Se envía angle.name a la API
)
```

---

## 15. Ambientes — BuildConfig

```kotlin
// build.gradle.kts (módulo app)
buildTypes {
    debug {
        applicationIdSuffix = ".debug"
        versionNameSuffix = "-debug"
        buildConfigField("String", "API_BASE_URL",
            "\"https://stg.chocapp.reddantechnology.com/api/v1/\"")
        buildConfigField("Boolean", "ENABLE_LOGS", "true")
    }
    release {
        isMinifyEnabled = true
        isShrinkResources = true
        buildConfigField("String", "API_BASE_URL",
            "\"https://chocapp.reddantechnology.com/api/v1/\"")
        buildConfigField("Boolean", "ENABLE_LOGS", "false")
        proguardFiles(getDefaultProguardFile("proguard-android-optimize.txt"), "proguard-rules.pro")
    }
}
```

---

## 16. Flujos críticos paso a paso

### Flujo A — Registro de usuario

```
1. RegisterScreen recopila: name, email, password, idType, idNumber, phoneNumber
2. Opcional: selección de foto de perfil (Uri)
3. AuthViewModel.register() → RegisterUseCase → AuthRepositoryImpl.register()
4. AuthRepositoryImpl construye @PartMap con los campos + MultipartBody.Part opcional
5. API retorna { data: { user, token } }
6. TokenStore.save(token) → se persiste en DataStore
7. FirebaseMessaging.getToken() → UpdateFcmTokenUseCase.launchSilently(fcmToken)
8. Navegar a: SelectVehicleScreen (onboarding) o Dashboard
```

### Flujo B — Registro de accidente

```
1. SelectVehicleScreen → vehicleId seleccionado
2. RegisterAccidentContextScreen:
   - description (mín 10 chars)
   - incidentDate (no puede ser futura)
   - incidentTime (HH:mm)
   - weatherCondition (selector)
   - roadCondition (selector)
   - locationAddress + latitude + longitude (GPS/mapa)
3. RegisterAccidentPhotosScreen:
   - MÍNIMO 4 ángulos de los hotspots
   - Compresión automática a < 800 KB por foto
4. IncidentViewModel.createIncident(draft) →
   CreateIncidentUseCase.invoke(draft) →
   IncidentRepositoryImpl.createIncident(draft)
5. Se construye @PartMap + List<MultipartBody.Part> con photos[0][file], photos[0][angle]...
6. API retorna { data: Incident } con status = "REPORTADO"
7. Evento IncidentCreated en la API → FCM push al usuario
8. Navegar a IncidentDetailScreen
```

### Flujo C — Generación y descarga de PDF

```kotlin
// En IncidentViewModel
fun exportPdf(incidentId: String) {
    viewModelScope.launch {
        _uiState.update { it.copy(isLoadingPdf = true) }
        try {
            val body = api.exportIncidentPdf(incidentId)
            val file = savePdfToDownloads(body, "reporte_$incidentId.pdf")
            _events.emit(UiEvent.OpenPdf(file))
        } catch (e: Exception) {
            _events.emit(UiEvent.ShowError("No se pudo generar el PDF"))
        } finally {
            _uiState.update { it.copy(isLoadingPdf = false) }
        }
    }
}

private suspend fun savePdfToDownloads(body: ResponseBody, filename: String): Uri {
    return withContext(Dispatchers.IO) {
        val resolver = context.contentResolver
        val values = ContentValues().apply {
            put(MediaStore.Downloads.DISPLAY_NAME, filename)
            put(MediaStore.Downloads.MIME_TYPE, "application/pdf")
        }
        val uri = resolver.insert(MediaStore.Downloads.EXTERNAL_CONTENT_URI, values)!!
        resolver.openOutputStream(uri)!!.use { out ->
            body.byteStream().use { it.copyTo(out) }
        }
        uri
    }
}
```

### Flujo D — Actualización del token FCM

```
El token FCM puede cambiar en cualquier momento. ChocFirebaseMessagingService.onNewToken()
se llama automáticamente por Firebase SDK cuando esto ocurre.

onNewToken(token) →  UpdateFcmTokenUseCase.launchSilently(token)
                  →  PUT /api/v1/auth/profile { "fcm_token": token }
                  →  La API actualiza users.fcm_token en BD

Sin este flujo, el usuario no recibirá notificaciones push después de reinstalar la app
o de que Firebase rote el token.
```

---

## Referencia rápida de campos críticos

| Campo API | Tipo | Notas |
|---|---|---|
| `name` | `String` | Un solo campo, no firstName+lastName |
| `id_type` | `String` enum | `CC`, `CE`, `PPT`, `PASAPORTE` |
| `incident_date` | `String` | Formato `YYYY-MM-DD` |
| `incident_time` | `String` | Formato `HH:mm` (24h) |
| `weather_condition` | `String` enum | `SOLEADO`, `LLUVIOSO`, `NUBLADO`, `NOCHE` |
| `road_condition` | `String` enum | `BUEN_ESTADO`, `HUMEDO`, `HUECOS`, `DERRUMBE` |
| `status` (incident) | `String` enum | `BORRADOR`, `REPORTADO`, `EN_REVISION`, `FINALIZADO` |
| `photos[N][file]` | `MultipartBody.Part` | Campo: `photos[0][file]`, `photos[1][file]`, ... |
| `photos[N][angle]` | `String` enum | `FRONT`, `REAR`, `LEFT`, `RIGHT`, etc. |
| `party_type` | `String` enum | `VEHICULO`, `PEATON`, `CICLISTA` (requerido en third-parties) |
| `driver_name` | `String?` | No `name` — campo correcto para terceros conductores |
| `driver_id` | `String?` | No `id_number` |
| `driver_phone` | `String?` | No `phone` |
| `maintenance_date` | `String` | No `date` — formato `YYYY-MM-DD` |
| `current_mileage` | `Int?` | No `mileage` |
| `document status` | `String` accessor | `VIGENTE`, `VENCE_PRONTO`, `VENCIDO` — calculado por la API |

---

*Documento técnico ChocApp v1.0 — Redd An Technology © 2026*  
*API: `https://chocapp.reddantechnology.com` · Staging: `https://stg.chocapp.reddantechnology.com`*
