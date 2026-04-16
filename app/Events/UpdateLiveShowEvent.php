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

    public $updateMessage;

    public function __construct($liveShowId, $status, $updateMessage)
    {
        $this->liveShowId = $liveShowId;
        $this->status = $status;
        $this->updateMessage = $updateMessage;
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

    public function broadcastWith()
    {
        return [
            'liveShowId' => $this->liveShowId,
            'status' => $this->status,
            'updateMessage' => $this->updateMessage,
        ];
    }
}
