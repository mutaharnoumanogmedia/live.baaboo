<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShowLiveShowQuizQuestionEvent implements ShouldBroadcastNow
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
    public $quiz_question_index;
    
    public function __construct($quiz_question, $live_show_id, $timer, $is_last = false, $quiz_question_index = 0 )
    {
        //
        $this->quiz_question = $quiz_question;
        $this->live_show_id = $live_show_id;
        $this->timer = $timer;
        $this->is_last = $is_last;
        $this->quiz_question_index = $quiz_question_index;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): Channel
    {
        return new Channel('live-show.' . $this->live_show_id);
    }

    public function broadcastAs(): string
    {
        return 'LiveShowQuizQuestionEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'quizQuestion' => $this->quiz_question,
            'quizQuestionIndex' => $this->quiz_question_index,
           
            'liveShowId' => $this->live_show_id,
            'timer' => $this->timer ?? 15,
            'isLast' => $this->is_last ?? false,
           
        ];
    }
}
