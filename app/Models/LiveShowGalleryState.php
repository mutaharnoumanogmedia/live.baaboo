<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveShowGalleryState extends Model
{
    protected $fillable = [
        'live_show_id',
        'is_visible',
        'gallery_media_id',
        'url',
        'media_type',
        'playback_started_at',
        'video_duration_seconds',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'playback_started_at' => 'datetime',
    ];

    public function liveShow(): BelongsTo
    {
        return $this->belongsTo(LiveShow::class);
    }

    public function galleryMedia(): BelongsTo
    {
        return $this->belongsTo(GalleryMedia::class);
    }
}
