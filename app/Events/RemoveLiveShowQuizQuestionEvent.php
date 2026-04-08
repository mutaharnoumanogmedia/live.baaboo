<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemoveLiveShowQuizQuestionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $queue = 'low';

    public $delay = 10;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quiz_question_id;

    public $live_show_id;

    public function __construct($quiz_question_id, $live_show_id)
    {
        $this->quiz_question_id = $quiz_question_id;
        $this->live_show_id = $live_show_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live-show.'.$this->live_show_id);
    }

    public function broadcastAs(): string
    {
        return 'RemoveLiveShowQuizQuestionEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'quizQuestionId' => $this->quiz_question_id,
            'liveShowId' => $this->live_show_id,
        ];
    }
}
