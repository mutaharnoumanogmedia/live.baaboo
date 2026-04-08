<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HeartReactionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'low';
    public $delay = 10;

    public function __construct(
        public string $liveShowId,
        public int $userId,
        public string $userName
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-show.' . $this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'HeartReactionEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
        ];
    }
}
