<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveShowGalleryMedia extends Model
{
    //
    protected $fillable = [
        'live_show_id',
        'gallery_media_id',
        'sort_order',
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
