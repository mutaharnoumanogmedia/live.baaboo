<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveShowQuiz extends Model
{
    use HasFactory;
    protected $fillable = ['live_show_id', 'question'];
    
    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }
    public function options()
    {
        return $this->hasMany(QuizOption::class, 'quiz_id');
    }
}
