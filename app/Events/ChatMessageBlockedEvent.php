<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// chat_filter_module: private-ish notice sent to the offending user telling them
// their message was blocked / they were timed out / banned, with a German reason.
class ChatMessageBlockedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;

    public $liveShowId;

    // chat_filter_module: one of block | timeout | ban
    public $action;

    public $message;

    public $mutedUntil;

    public function __construct($userId, $liveShowId, string $action, string $message, $mutedUntil = null)
    {
        $this->userId = $userId;
        $this->liveShowId = $liveShowId;
        $this->action = $action;
        $this->message = $message;
        $this->mutedUntil = $mutedUntil;
    }

    public function broadcastOn()
    {
        // chat_filter_module: per-user channel so only the offender is notified
        return new Channel('chat-filter-user.'.$this->userId);
    }

    public function broadcastAs()
    {
        return 'ChatMessageBlockedEvent';
    }

    public function broadcastWith()
    {
        return [
            'userId' => $this->userId,
            'liveShowId' => $this->liveShowId,
            'action' => $this->action,
            'message' => $this->message,
            'mutedUntil' => $this->mutedUntil,
        ];
    }
}
