<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcasts admin-facing state changes for a live show so that every open
 * stream-management tab/screen stays in sync in real time, regardless of which
 * admin triggered the change.
 *
 * Dispatched as ShouldBroadcastNow so the sync happens instantly without
 * relying on a queue worker.
 *
 * The `type` discriminates the kind of change and `payload` carries the data:
 *  - 'status'  => ['status' => 'scheduled|live|completed']
 *  - 'winners' => ['winners_announced' => bool]
 *  - 'quiz'    => ['action' => 'shown|hidden', 'quizId' => int, 'seconds' => int|null]
 */
class LiveShowAdminStateEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'high';

    public function __construct(
        public string $liveShowId,
        public string $type,
        public array $payload = []
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('live-show-admin.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowAdminStateEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'liveShowId' => $this->liveShowId,
            'type' => $this->type,
            'payload' => $this->payload,
        ];
    }
}
