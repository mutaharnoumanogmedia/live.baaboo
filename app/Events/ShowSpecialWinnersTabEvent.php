<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShowSpecialWinnersTabEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'high';

    public string $liveShowId;

    public array $winnersData;

    public function __construct(string $liveShowId, array $winnersData = [])
    {
        $this->liveShowId = $liveShowId;
        $this->winnersData = $winnersData;
    }

    public function broadcastOn()
    {
        return new Channel('live-show.'.$this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'ShowSpecialWinnersTabEvent';
    }

    public function broadcastWith()
    {
        return [
            'liveShowId' => $this->liveShowId,
            'winnersData' => $this->winnersData,
        ];
    }
}
