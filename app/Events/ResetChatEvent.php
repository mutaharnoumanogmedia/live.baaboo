<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResetChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveShowId;

    public function __construct($liveShowId)
    {
        $this->liveShowId = $liveShowId;
    }

    public function broadcastOn()
    {
        return new Channel('reset-chat.' . $this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'ResetChatEvent';
    }
}
