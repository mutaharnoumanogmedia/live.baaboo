<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveShowChatStatusUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'low';
    public $delay = 10;
    public string $liveShowId;

    public bool $chatEnabled;

    public function __construct(string $liveShowId, bool $chatEnabled)
    {
        $this->liveShowId = $liveShowId;
        $this->chatEnabled = $chatEnabled;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('live-show-chat-status.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowChatStatusUpdatedEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'liveShowId' => $this->liveShowId,
            'chatEnabled' => $this->chatEnabled,
        ];
    }
}
