<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserBlockFromLiveShowEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $liveShowId;

    public $userId;

    public $isBlocked;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($liveShowId, $userId, $isBlocked)
    {
        $this->liveShowId = $liveShowId;
        $this->userId = $userId;
        $this->isBlocked = $isBlocked;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('user-block-from-live-show.'.$this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'UserBlockFromLiveShowEvent';
    }

    public function broadcastWith()
    {
        return [
            'liveShowId' => $this->liveShowId,
            'userId' => $this->userId,
            'isBlocked' => $this->isBlocked,
        ];
    }
}
