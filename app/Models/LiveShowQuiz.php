<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveShowQuiz extends Model
{
    use HasFactory;

    protected $fillable = ['live_show_id', 'question', 'created_by', 'has_shown'];

    protected $casts = [
        'has_shown' => 'boolean',
    ];

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }

    public function options()
    {
        return $this->hasMany(QuizOption::class, 'quiz_id');
    }

    public function userQuizzes()
    {
        return $this->hasMany(UserQuiz::class, 'quiz_id');
    }
}
