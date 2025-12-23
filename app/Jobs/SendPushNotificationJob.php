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

    public function __construct(
        public int $userId,
        public string $title,
        public string $message,
        public array $data = []
    ) {}

    public function handle(): void
    {
        $query = PushSubscription::query();

        if ($this->userId > 0) {
            $query->where('user_id', $this->userId);
        }

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

        foreach ($subscriptions as $sub) {
            $webPush->sendOneNotification(
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

        $webPush->flush();
    }
}
