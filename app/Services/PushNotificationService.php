<?php

namespace App\Services;

use App\Jobs\SendPushNotificationJob;

class PushNotificationService
{
    /**
     * Send a push notification.
     *
     * @param int    $userId  0 = broadcast to all, >0 = single user
     * @param string $title
     * @param string $message
     * @param array  $data
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        array $data = []
    ): void {
        SendPushNotificationJob::dispatch(
            $userId,
            $title,
            $message,
            $data
        )->onQueue('push');
    }
}
