<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveShowEndMedia extends Model
{
    protected $table = 'live_show_end_media';

    protected $fillable = [
        'live_show_id',
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

    public function galleryMedia(): BelongsTo
    {
        return $this->belongsTo(GalleryMedia::class);
    }
}
