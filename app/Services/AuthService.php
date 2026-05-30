<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly StorageService $storageService,
    ) {}

    /**
     * @return array{0: User, 1: string}
     */
    public function register(array $data, ?UploadedFile $profilePic = null): array
    {
        $profilePicUrl = null;
        if ($profilePic) {
            $profilePicUrl = $this->storageService->uploadFile($profilePic, 'users/avatars');
        }

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => $data['password'],
            'id_type'         => $data['id_type'],
            'id_number'       => $data['id_number'],
            'phone_number'    => $data['phone_number'],
            'terms_accepted'  => true,
            'profile_pic_url' => $profilePicUrl,
        ]);

        $token = $user->createToken('mobile', ['*'], now()->addDays(90))->plainTextToken;

        return [$user, $token];
    }

    /**
     * @return array{0: User, 1: string}
     * @throws ValidationException
     */
    public function loginWithCredentials(string $email, string $password): array
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no coinciden con nuestros registros.'],
            ])->status(401);
        }

        $user  = Auth::user();
        $token = $user->createToken('mobile', ['*'], now()->addDays(90))->plainTextToken;

        return [$user, $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function updateProfile(User $user, array $data, ?UploadedFile $profilePic = null): User
    {
        if ($profilePic) {
            $data['profile_pic_url'] = $this->storageService->uploadFile($profilePic, 'users/avatars');
        }

        $user->update(array_filter($data, fn($v) => !is_null($v)));

        return $user->fresh();
    }

    public function updateFcmToken(User $user, string $token): void
    {
        $user->update(['fcm_token' => $token]);
    }
}
