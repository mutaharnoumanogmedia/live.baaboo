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

    /**
     * Send the same push notification to a specific set of users.
     *
     * Useful for notifying every player of a live show at once. The list is
     * de-duplicated and emptied of invalid IDs before queueing a single job.
     *
     * @param array<int> $userIds
     * @param string     $title
     * @param string     $message
     * @param array      $data
     *
     * @return int The number of distinct users targeted (0 if none).
     */
    public static function sendToUsers(
        array $userIds,
        string $title,
        string $message,
        array $data = []
    ): int {
        // Normalise the incoming IDs: keep only positive integers and remove
        // duplicates so we never queue an empty or wasteful job.
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
            $data
        )->onQueue('high');

        return count($userIds);
    }
}
