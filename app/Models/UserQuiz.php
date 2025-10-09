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
}
