<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// chat_filter_module: Tier 4 watchlist highlight pushed to the moderator stream
// so the editorial team can answer criticism in chat instead of blocking it.
class ChatFilterFlaggedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel('chat-filter-flagged.'.$this->data['live_show_id']);
    }

    public function broadcastAs()
    {
        return 'ChatFilterFlaggedEvent';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
