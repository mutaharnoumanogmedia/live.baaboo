<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'low';

    public $delay = 10;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $announcement;

    public function __construct($announcement)
    {
        $this->announcement = $announcement;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('announcement-event-channel');
    }

    public function broadcastAs()
    {
        return 'AnnouncementEvent';
    }
}
