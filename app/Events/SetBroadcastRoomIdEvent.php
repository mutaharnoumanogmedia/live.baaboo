<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SetBroadcastRoomIdEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'low';

    public $delay = 10;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $liveShowId;

    public $broadcastRoomId;

    public function __construct($liveShowId, $broadcastRoomId)
    {
        $this->liveShowId = $liveShowId;
        $this->broadcastRoomId = $broadcastRoomId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('set-broadcast-room-id.'.$this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'SetBroadcastRoomIdEvent';
    }
}
