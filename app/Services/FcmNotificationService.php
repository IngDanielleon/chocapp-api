<?php

namespace App\Services;

use App\Enums\NotificationTypeEnum;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmNotificationService
{
    private string $projectId;
    private string $serverKey;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id') ?? '';
        $this->serverKey = config('services.fcm.server_key') ?? '';
    }

    public function send(User $user, string $title, string $body, array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $data['type'] ?? NotificationTypeEnum::INFO->value,
            'data'    => $data,
        ]);

        if (empty($user->fcm_token) || empty($this->projectId)) {
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
            Log::warning('ChocApp: FCM push failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
