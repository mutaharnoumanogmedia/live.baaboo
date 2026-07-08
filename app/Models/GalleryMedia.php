<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'type',
        'original_name',
        'file_size',
        'mime_type',
        'title',
        'thumbnail',
        'total_seconds',
    ];

    public function liveShows()
    {
        return $this->belongsToMany(LiveShow::class, 'live_show_gallery_media')
            ->withPivot(['sort_order', 'before_question'])
            ->wherePivotNull('before_question')
            ->withTimestamps();
    }

    public function getUrlAttribute(): string
    {
        return ($this->path);
    }

    // public function getFullPath(): string
    // {
    //     return Storage::disk('public')->path($this->path);
    // }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
