<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LiveShowMediaHidden implements ShouldBroadcast
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
        return new Channel('live-show-media-hidden.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowMediaHidden';
    }

    public function broadcastWith(): array
    {
        return [
            'liveShowId' => $this->liveShowId,
        ];
    }
}
