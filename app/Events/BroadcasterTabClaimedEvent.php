<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired whenever a browser tab claims the stream broadcaster page for a
 * specific live show. Every open broadcaster tab listens for this event on
 * the existing `live-show.{id}` Pusher channel and compares the broadcast
 * `tab_id` against the id it generated for itself at page load:
 *
 *  - If they match → this tab is now the active broadcaster.
 *  - If they differ → this tab has been superseded by a newer one and must
 *    immediately stop streaming and show a blocking overlay.
 *
 * The event is dispatched as `ShouldBroadcastNow` so the kick-out happens
 * with no queue lag.
 */
class BroadcasterTabClaimedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'high';

    public function __construct(
        public int $liveShowId,
        public string $tabId
    ) {}

    /**
     * Broadcast on the same channel the broadcaster page already subscribes
     * to, so we don't need a second Pusher subscription on the client.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live-show.'.$this->liveShowId);
    }

    public function broadcastAs(): string
    {
        return 'BroadcasterTabClaimedEvent';
    }

    /**
     * Payload sent to the browser.
     */
    public function broadcastWith(): array
    {
        return [
            'live_show_id' => $this->liveShowId,
            'tab_id' => $this->tabId,
        ];
    }
}
