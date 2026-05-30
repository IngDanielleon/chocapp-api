<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class SocialAuthService
{
    /**
     * Find or create a user from social provider data.
     *
     * @return array{0: User, 1: string}
     */
    public function findOrCreate(string $provider, array $socialData): array
    {
        $user = User::where('social_provider', $provider)
            ->where('social_id', $socialData['id'])
            ->first();

        if (!$user) {
            $user = User::where('email', $socialData['email'])->first();

            if ($user) {
                $user->update([
                    'social_provider' => $provider,
                    'social_id'       => $socialData['id'],
                    'profile_pic_url' => $user->profile_pic_url ?? $socialData['avatar'] ?? null,
                ]);
            } else {
                $user = User::create([
                    'name'            => $socialData['name'],
                    'email'           => $socialData['email'],
                    'password'        => Str::password(32),
                    'id_type'         => 'CC',
                    'id_number'       => 'SOCIAL-' . Str::random(10),
                    'phone_number'    => $socialData['phone'] ?? '',
                    'terms_accepted'  => true,
                    'social_provider' => $provider,
                    'social_id'       => $socialData['id'],
                    'profile_pic_url' => $socialData['avatar'] ?? null,
                ]);
            }
        }

        $token = $user->createToken('mobile', ['*'], now()->addDays(90))->plainTextToken;

        return [$user, $token];
    }
}
