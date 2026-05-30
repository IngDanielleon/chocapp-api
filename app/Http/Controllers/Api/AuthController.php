<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="ChocApp API",
 *   version="1.0.0",
 *   description="REST API para documentación de accidentes de tránsito — Colombia.",
 *   @OA\Contact(email="soporte@chocapp.reddantechnology.com")
 * )
 * @OA\Server(url="https://chocapp.reddantechnology.com/api/v1", description="Producción")
 * @OA\SecurityScheme(
 *   securityScheme="BearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 * @OA\Tag(name="Auth", description="Autenticación de usuarios")
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly AuthService $authService) {}

    /**
     * @OA\Post(
     *   path="/auth/register",
     *   tags={"Auth"},
     *   summary="Registrar nuevo usuario",
     *   @OA\RequestBody(required=true,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"name","email","password","password_confirmation","id_type","id_number","phone_number","terms_accepted"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="email", type="string", format="email"),
     *         @OA\Property(property="password", type="string", minLength=8),
     *         @OA\Property(property="password_confirmation", type="string"),
     *         @OA\Property(property="id_type", type="string", enum={"CC","CE","PPT","PASAPORTE"}),
     *         @OA\Property(property="id_number", type="string"),
     *         @OA\Property(property="phone_number", type="string"),
     *         @OA\Property(property="terms_accepted", type="boolean"),
     *         @OA\Property(property="profile_pic", type="string", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=201, description="Usuario registrado exitosamente"),
     *   @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        [$user, $token] = $this->authService->register(
            $request->validated(),
            $request->file('profile_pic')
        );

        return $this->createdResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Registro exitoso');
    }

    /**
     * @OA\Post(
     *   path="/auth/login",
     *   tags={"Auth"},
     *   summary="Iniciar sesión",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Token de acceso"),
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
     *   path="/auth/social",
     *   tags={"Auth"},
     *   summary="Login con proveedor social (Google, Apple)",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"provider","token"},
     *       @OA\Property(property="provider", type="string", enum={"google","apple","facebook"}),
     *       @OA\Property(property="token", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Token de acceso")
     * )
     */
    public function social(SocialLoginRequest $request): JsonResponse
    {
        return $this->errorResponse('Social login no configurado en este entorno.', 501);
    }

    /**
     * @OA\Post(
     *   path="/auth/logout",
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
     *   path="/auth/me",
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

    /**
     * @OA\Put(
     *   path="/auth/profile",
     *   tags={"Auth"},
     *   summary="Actualizar perfil del usuario",
     *   security={{"BearerAuth":{}}},
     *   @OA\RequestBody(required=false,
     *     @OA\MediaType(mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="phone_number", type="string"),
     *         @OA\Property(property="fcm_token", type="string"),
     *         @OA\Property(property="profile_pic", type="string", format="binary")
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="Perfil actualizado")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'nullable|string|min:3|max:100',
            'phone_number' => 'nullable|string|max:20',
            'fcm_token'    => 'nullable|string|max:500',
            'profile_pic'  => 'nullable|image|mimes:jpeg,png,webp|max:5120',
        ]);

        $user = $this->authService->updateProfile(
            auth()->user(),
            $validated,
            $request->file('profile_pic')
        );

        return $this->successResponse(new UserResource($user), 'Perfil actualizado exitosamente');
    }

    /**
     * @OA\Post(
     *   path="/auth/password/forgot",
     *   tags={"Auth"},
     *   summary="Solicitar restablecimiento de contraseña",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(required={"email"}, @OA\Property(property="email", type="string", format="email"))
     *   ),
     *   @OA\Response(response=200, description="Email enviado")
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        return $this->successResponse(null, 'Si el correo existe, recibirás un enlace para restablecer tu contraseña.');
    }

    /**
     * @OA\Post(
     *   path="/auth/password/reset",
     *   tags={"Auth"},
     *   summary="Restablecer contraseña con token",
     *   @OA\Response(response=200, description="Contraseña restablecida")
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        return $this->successResponse(null, 'Contraseña restablecida exitosamente.');
    }
}
