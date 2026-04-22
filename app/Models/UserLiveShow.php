<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserLiveShow extends Pivot
{
    use HasFactory;

    protected $table = 'user_live_shows';

    protected $fillable = [
        'user_id',
        'live_show_id',
        'score',
        'status',
        'is_winner',
        'prize_won',
        'is_online',
        'created_at',
    ];

    public $timestamps = true;

    // change the score , calculate from the user_quiz_responses table sum of response_score
    public function getScoreAttribute()
    {
        return $this->userQuizResponses()->sum('response_score');
    }
}
