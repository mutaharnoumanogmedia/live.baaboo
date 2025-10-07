<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveShowOnlineUsersEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $activeUsers;
    public string $liveShowId;
    public int $timer = 15;

    public function __construct($activeUsers, string $liveShowId, int $timer = 15)
    {
        $this->activeUsers = $activeUsers;
        $this->liveShowId = $liveShowId;
        $this->timer = $timer;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('live-show-online-users.' . $this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowOnlineUsersEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'activeUsers' => $this->activeUsers,
            'liveShowId' => $this->liveShowId,
            'timer' => $this->timer,
        ];
    }
}
