<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuiz extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'live_show_id',
        'quiz_id',
        'score',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }
    public function quiz()
    {
        return $this->belongsTo(LiveShowQuiz::class, 'quiz_id');
    }

    public function userQuizResponses()
    {
        return $this->hasMany(UserQuizResponse::class, 'user_quiz_id');
    }
}
