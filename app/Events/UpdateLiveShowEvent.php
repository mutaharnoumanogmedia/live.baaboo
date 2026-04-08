<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateLiveShowEvent implements ShouldBroadcast
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

    public $status;

    public function __construct($liveShowId, $status)
    {
        $this->liveShowId = $liveShowId;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('live-show.'.$this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'UpdateLiveShowEvent';
    }
}
