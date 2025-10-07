<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShowLiveShowQuizQuestionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $quiz_question;
    public $live_show_id;
    public $timer;
    public $is_last;
    public function __construct($quiz_question, $live_show_id, $timer, $is_last = false)
    {
        //
        $this->quiz_question = $quiz_question;
        $this->live_show_id = $live_show_id;
        $this->timer = $timer;
        $this->is_last = $is_last;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live-show-quiz.' . $this->live_show_id);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowQuizQuestionEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'quizQuestion' => $this->quiz_question,
            'liveShowId' => $this->live_show_id,
            'timer' => $this->timer ?? 15,
            'isLast' => $this->is_last ?? false
        ];
    }
}
