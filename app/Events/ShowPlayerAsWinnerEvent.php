<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShowPlayerAsWinnerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $liveShowId;

    public function __construct($userId, $liveShowId)
    {
        //
        $this->userId = $userId;
        $this->liveShowId = $liveShowId;
    }

    public function broadcastOn()
    {
        return new Channel('live-show-winner-user.' . $this->liveShowId . '.' . $this->userId);
    }
    public function broadcastAs()
    {
        return 'ShowPlayerAsWinnerEvent';
    }
    public function broadcastWith()
    {
        return [
            'userId' => $this->userId,
            'liveShowId' => $this->liveShowId,
        ];
    }
}
