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

    /**
     * Gallery media configured to play *before* this question.
     *
     * Stored in the shared `live_show_gallery_media` pivot where the
     * `before_question` column points at this quiz.
     */
    public function questionMedia()
    {
        return $this->belongsToMany(GalleryMedia::class, 'live_show_gallery_media', 'before_question', 'gallery_media_id')
            ->withPivot(['id', 'sort_order', 'live_show_id', 'media_played', 'before_question'])
            ->orderBy('live_show_gallery_media.sort_order')
            ->withTimestamps();
    }
}
