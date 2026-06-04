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
        'winner_prize_id', // new column for winner prize id
        'discount_code', // new column for discount code
        'created_at',
    ];

    public $timestamps = true;

    // change the score , calculate from the user_quiz_responses table sum of response_score
    public function getScoreAttribute()
    {
        $userId = $this->user_id;
        $liveShowId = $this->live_show_id;

        $sum = \DB::table('user_quiz_responses')
            ->join('live_show_quizzes', 'user_quiz_responses.quiz_id', '=', 'live_show_quizzes.id')
            ->where('user_quiz_responses.user_id', $userId)
            ->where('live_show_quizzes.live_show_id', $liveShowId)
            ->sum('user_quiz_responses.response_score');

        return round($sum, 2);

    }

    public function userQuizResponses()
    {
        return $this->hasMany(UserQuizResponse::class, 'user_id', 'user_id')
            ->whereHas('quiz', function ($query) {
                $query->where('live_show_id', $this->live_show_id);
            });

    }

    public function userQuiz()
    {
        return $this->hasOne(UserQuiz::class, 'user_id', 'user_id');
    }

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class, 'live_show_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function winnerPrize()
    {
        return $this->belongsTo(LiveShowWinnerPrize::class, 'winner_prize_id');
    }
}
