<?php

namespace App\Services;

use App\Jobs\SendPushNotificationJob;
use App\Models\PushSubscription;

class PushNotificationService
{
    /**
     * Count devices in push_subscriptions for the given targeting rule.
     *
     * @param int      $userId         0 = all devices, >0 = that user's devices only
     * @param int|null $subscriptionId When set, counts only that subscription row
     */
    public static function countTargetDevices(int $userId, ?int $subscriptionId = null): int
    {
        $query = PushSubscription::query();

        if ($subscriptionId) {
            return $query->whereKey($subscriptionId)->count();
        }

        if ($userId > 0) {
            $query->where('user_id', $userId);
        }

        return $query->count();
    }

    /**
     * Send a push notification to matching rows in push_subscriptions.
     *
     * @param int      $userId         0 = every saved device, >0 = single user's devices
     * @param string   $title
     * @param string   $message
     * @param array    $data           Optional payload; include 'url' for click-through
     * @param int|null $subscriptionId When set, send only to this subscription row
     */
    public static function send(
        int $userId,
        string $title,
        string $message,
        array $data = [],
        ?int $subscriptionId = null,
    ): void {
        SendPushNotificationJob::dispatch(
            $userId,
            $title,
            $message,
            $data,
            $subscriptionId,
        )->onQueue('high');
    }

    /**
     * Send the same push notification to a specific set of users.
     *
     * @param array<int> $userIds
     *
     * @return int The number of distinct users targeted (0 if none).
     */
    public static function sendToUsers(
        array $userIds,
        string $title,
        string $message,
        array $data = []
    ): int {
        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            static fn (int $id): bool => $id > 0
        )));

        if (empty($userIds)) {
            return 0;
        }

        SendPushNotificationJob::dispatch(
            $userIds,
            $title,
            $message,
            $data,
        )->onQueue('high');

        return count($userIds);
    }
}
