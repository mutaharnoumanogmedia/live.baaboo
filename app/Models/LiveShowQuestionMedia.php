<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated "Media before a question" is now stored in the
 * `live_show_gallery_media` table via the nullable `before_question` column
 * (see App\Models\LiveShowGalleryMedia). This model and its
 * `live_show_question_media` table are no longer used by the application and
 * are kept only for backwards compatibility / historical data.
 */
class LiveShowQuestionMedia extends Model
{
    protected $table = 'live_show_question_media';

    protected $fillable = [
        'live_show_id',
        'quiz_id',
        'gallery_media_id',
        'sort_order',
        'media_played',
    ];

    protected $casts = [
        'media_played' => 'boolean',
    ];

    public function liveShow(): BelongsTo
    {
        return $this->belongsTo(LiveShow::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LiveShowQuiz::class, 'quiz_id');
    }

    public function galleryMedia(): BelongsTo
    {
        return $this->belongsTo(GalleryMedia::class);
    }
}
