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

    public $queue = 'low';

    public $delay = 10;

    public function __construct(
        public string $liveShowId,
        public string $url,
        public string $type,
        public ?string $playbackStartedAt = null,
        public ?int $videoDurationSeconds = null
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-show.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'ShowGalleryImageEvent';
    }

    public function broadcastWith(): array
    {
        $data = [
            'url' => $this->url,
            'type' => $this->type,
        ];
        if ($this->playbackStartedAt !== null) {
            $data['playback_started_at'] = $this->playbackStartedAt;
        }
        if ($this->videoDurationSeconds !== null) {
            $data['video_duration_seconds'] = $this->videoDurationSeconds;
        }

        return $data;
    }
}
