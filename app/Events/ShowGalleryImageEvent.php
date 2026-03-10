<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShowGalleryImageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $liveShowId,
        public string $url,
        public string $type
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-show-gallery-image.' . $this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'ShowGalleryImageEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'url' => $this->url,
            'type' => $this->type,
        ];
    }
}
