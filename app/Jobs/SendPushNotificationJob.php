<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int|array $userId Targeting rule for the notification:
     *                          - 0           => broadcast to every saved subscription
     *                          - int (>0)    => only that single user's devices
     *                          - array<int>  => only the devices of the given users
     *                                           (used when notifying all players of a show)
     */
    public function __construct(
        public int|array $userId,
        public string $title,
        public string $message,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $query = PushSubscription::query();

        // Resolve which subscriptions should receive this notification based on
        // the targeting rule passed into the job.
        if (is_array($this->userId)) {
            // A list of user IDs (e.g. all players of a live show).
            $query->whereIn('user_id', $this->userId);
        } elseif ($this->userId > 0) {
            // A single user.
            $query->where('user_id', $this->userId);
        }
        // When $userId === 0 we leave the query unfiltered to broadcast to all.

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => env('APP_URL'),
                'publicKey' => env('VAPID_PUBLIC_KEY'),
                'privateKey' => env('VAPID_PRIVATE_KEY'),
            ],
        ]);

        // Map each endpoint to its DB record so we can clean up dead
        // subscriptions after the push provider reports back.
        $subscriptionsByEndpoint = $subscriptions->keyBy('endpoint');

        // Queue every notification first, then flush them in one batch.
        foreach ($subscriptions as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                ]),
                json_encode([
                    'title' => $this->title,
                    'body'  => $this->message,
                    'url'   => $this->data['url'] ?? '/',
                    'data'  => $this->data,
                ])
            );
        }

        // flush() returns a report per notification. We use it to drop
        // subscriptions that are no longer valid (e.g. the user removed the
        // app or the browser revoked the subscription).
        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $endpoint = $report->getEndpoint();
                optional($subscriptionsByEndpoint->get($endpoint))->delete();
            }
        }
    }
}
