<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizResponse extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_quiz_id',
        'quiz_option_id',
        'quiz_id',
        'user_id',
        'is_correct',
        'user_response',
        'seconds_to_submit',
    ];
    public function userQuiz()
    {
        return $this->belongsTo(UserQuiz::class, 'user_quiz_id');
    }
    public function quizOption()
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }
    public function quiz()
    {
        return $this->belongsTo(LiveShowQuiz::class, 'quiz_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
