<?php

namespace App\Services;

use App\Data\NotificationPayload;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendPushNotificationJob;
use App\Models\User;

class NotificationService
{
    public static function send(
        User $user,
        string $title,
        string $message,
        array $channels = ['email'],
        array $data = []
    ): void {
        $payload = new NotificationPayload(
            title: $title,
            message: $message,
            data: $data
        );

        foreach ($channels as $channel) {
            match ($channel) {
                'email' => SendEmailNotificationJob::dispatch($user->id, $payload),
                'push'  => SendPushNotificationJob::dispatch($user->id, $payload),
                default => null
            };
        }
    }
}
