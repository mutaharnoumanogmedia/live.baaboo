<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveShowMediaPlayed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'high';

    public $delay = 1;

    public function __construct(public string $liveShowId)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live-show-media-played.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowMediaPlayed';
    }

    public function broadcastWith(): array
    {
        return [
            'liveShowId' => $this->liveShowId,
        ];
    }
}
