<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpecialQuizResponse extends Model
{
    use HasFactory;

    protected $table = 'user_special_quiz_responses';

    protected $fillable = [
        'quiz_id',
        'user_id',
        'quiz_option_id',
        'is_correct',
        'user_response',
        'seconds_to_submit',
        'response_score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'seconds_to_submit' => 'float',
        'response_score' => 'float',
    ];

    public function quiz()
    {
        return $this->belongsTo(LiveShowQuiz::class, 'quiz_id');
    }

    public function quizOption()
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
