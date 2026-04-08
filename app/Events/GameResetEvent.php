<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameResetEvent implements ShouldBroadcast
{
    public $queue = 'low';

    public $delay = 10;

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveShowId;

    public function __construct($liveShowId)
    {
        $this->liveShowId = $liveShowId;
    }

    public function broadcastOn()
    {
        return new Channel('live-show.'.$this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'GameResetEvent';
    }
}
