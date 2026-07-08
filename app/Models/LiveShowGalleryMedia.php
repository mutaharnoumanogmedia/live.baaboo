<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveShowGalleryMedia extends Model
{
    protected $table = 'live_show_gallery_media';

    protected $fillable = [
        'live_show_id',
        'gallery_media_id',
        'before_question',
        'sort_order',
        'media_played',
        'play_with_live',
    ];

    protected $casts = [
        'media_played' => 'boolean',
        'play_with_live' => 'boolean',
    ];

    public function liveShow(): BelongsTo
    {
        return $this->belongsTo(LiveShow::class);
    }

    public function galleryMedia(): BelongsTo
    {
        return $this->belongsTo(GalleryMedia::class);
    }

    /**
     * The quiz this media plays before, when acting as a "before question"
     * attachment. Null for regular show-wide attachments.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LiveShowQuiz::class, 'before_question');
    }
}
