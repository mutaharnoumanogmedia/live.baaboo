<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveShowQuizUserResponses implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $liveShowId;
    public $quizId;
    public $statistics;
    public $correctOptionId;

    public function __construct($liveShowId, $quizId, $statistics, $correctOptionId)
    {
        $this->liveShowId = $liveShowId;
        $this->quizId = $quizId;
        $this->statistics = $statistics;
        $this->correctOptionId = $correctOptionId;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('live-show-quiz-users-responses.' . $this->liveShowId);
    }

    public function broadcastAs()
    {
        return 'LiveShowQuizUserResponses';
    }
    public function broadcastWith()
    {
        return [
            'liveShowId' => $this->liveShowId,
            'quizId' => $this->quizId,
            'statistics' => $this->statistics,
            'correctOptionId' => $this->correctOptionId,
        ];
    }
}
