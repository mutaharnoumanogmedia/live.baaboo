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
    ];
}
