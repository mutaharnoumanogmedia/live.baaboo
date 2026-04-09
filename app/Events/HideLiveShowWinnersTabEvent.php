<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HideLiveShowWinnersTabEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'low';
    public $delay = 10;

    public string $liveShowId;

    public function __construct(string $liveShowId)
    {
        $this->liveShowId = $liveShowId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('live-show.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'HideLiveShowWinnersTabEvent';
    }
}
