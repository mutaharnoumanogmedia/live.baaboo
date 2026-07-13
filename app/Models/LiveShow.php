<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveShow extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'stream_id',
        'description',
        'scheduled_at',
        'status',
        'thumbnail',
        'is_test_show',

        'host_name',
        'prize_amount',
        'currency',
        'max_winners',
        'special_max_winners',
        'max_players',
        'chat_enabled',
        'winners_announced',
        'special_winners_announced',
        'created_by',

        'start_time',
        'end_time',
        'media_visible',

        // Unique identifier of the browser tab that currently owns the
        // broadcaster page. Only the tab whose id matches this value is
        // allowed to keep broadcasting; any other tab is "superseded".
        'host_browser_tab',

    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'prize_amount' => 'float',
        'max_players' => 'integer',
        'is_test_show' => 'boolean',
        'chat_enabled' => 'boolean',
        'winners_announced' => 'boolean',
        'special_winners_announced' => 'boolean',
        'special_max_winners' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'media_visible' => 'boolean',
    ];

    // scope test show
    public function scopeTestShow($query)
    {
        return $query->where('is_test_show', true);
    }

    // scope not test show
    public function scopeNotTestShow($query)
    {
        return $query->where('is_test_show', false);
    }

    protected $appends = ['stream_link'];

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<=', now());
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Next public live or scheduled show for the homepage (excludes test shows).
     */
    public static function currentForHomepage(): ?self
    {
        return static::query()
            ->where(function ($query) {
                $query->where('status', 'live')
                    ->orWhere(function ($q) {
                        $q->where('status', 'scheduled')
                            ->whereDate('scheduled_at', '>=', now()->toDateString());
                    });
            })
            ->orderBy('scheduled_at', 'asc')
            ->notTestShow()
            ->first();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_live_shows')
            ->using(UserLiveShow::class)   // tell Laravel to use pivot model
            ->withPivot(['score', 'status', 'is_online', 'is_winner', 'prize_won']) // include extra fields
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(LiveShowMessages::class, 'live_show_id');
    }

    public function quizzes()
    {
        return $this->hasMany(LiveShowQuiz::class, 'live_show_id');
    }

    public function mainQuizzes()
    {
        return $this->hasMany(LiveShowQuiz::class, 'live_show_id')->where('is_special', false);
    }

    public function specialQuizzes()
    {
        return $this->hasMany(LiveShowQuiz::class, 'live_show_id')->where('is_special', true);
    }

    public function winnerPrizes()
    {
        return $this->hasMany(LiveShowWinnerPrize::class)->orderBy('rank');
    }

    public function specialGifts()
    {
        return $this->hasMany(SpecialGift::class)->orderBy('rank');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'live_show_block_users')
            ->withPivot(['user_id'])
            ->withTimestamps()
            ->orderBy('live_show_block_users.created_at', 'desc');
    }

    public function galleryMedia()
    {
        return $this->belongsToMany(GalleryMedia::class, 'live_show_gallery_media')
            ->withPivot(['id', 'sort_order', 'media_played', 'play_with_live', 'before_question'])
            ->wherePivotNull('before_question')
            ->orderBy('live_show_gallery_media.sort_order')
            ->withTimestamps();
    }

    public function galleryMediaItems()
    {
        return $this->hasMany(LiveShowGalleryMedia::class)->with('galleryMedia')
            ->whereNull('before_question')
            ->orderBy('live_show_gallery_media.sort_order');

    }

    public function galleryState()
    {
        return $this->hasOne(LiveShowGalleryState::class);
    }

    /**
     * Question-level media attachments for this show (media shown before a
     * specific quiz question). These live in the same `live_show_gallery_media`
     * table as show-wide media but have a non-null `before_question`.
     */
    public function questionMedia()
    {
        return $this->hasMany(LiveShowGalleryMedia::class)->whereNotNull('before_question');
    }

    /**
     * Media that plays after all quiz questions in the show flow.
     */
    public function endMedia()
    {
        return $this->belongsToMany(GalleryMedia::class, 'live_show_end_media')
            ->withPivot(['id', 'sort_order', 'media_played'])
            ->orderBy('live_show_end_media.sort_order')
            ->withTimestamps();
    }

    public function endMediaItems()
    {
        return $this->hasMany(LiveShowEndMedia::class)->with('galleryMedia')
            ->orderBy('live_show_end_media.sort_order');
    }

    /**
     * Whether a gallery media item is attached to this show at all — either
     * as general show media, before one of the show's questions, or at the
     * end of all questions. Used to authorise showing media on the live stream.
     */
    public function isGalleryMediaAttached($mediaId): bool
    {
        $mediaId = (int) $mediaId;

        return $this->galleryMedia()->where('gallery_media.id', $mediaId)->exists()
            || $this->questionMedia()->where('gallery_media_id', $mediaId)->exists()
            || $this->endMedia()->where('gallery_media.id', $mediaId)->exists();
    }

    // public function getStreamIdAttribute()
    // {
    //     return $this->extractYouTubeId($this->stream_link);
    // }

    public function getStreamLinkAttribute()
    {
        return route('live-show', $this->id);
    }

    public function extractYouTubeId(string $url): ?string
    {
        // Handle HTML entities like &amp; in the URL
        $url = html_entity_decode($url);

        // Match multiple possible YouTube URL formats
        $pattern = '%(?:youtube\.com/(?:.*v=|(?:embed|shorts)/)|youtu\.be/)([^?&/]+)%i';

        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function clearGameShowUsers($liveShowId)
    {
        // Clear users from the specified live show
        try {
            $liveShow = LiveShow::with('users')->find($liveShowId);

            $liveShowUsers = $liveShow->users()->get();

            foreach ($liveShowUsers as $user) {
                UserQuiz::where('user_id', $user->id)
                    ->where('live_show_id', $liveShowId)
                    ->with('userQuizResponses')
                    ->delete();
            }
            $liveShow->users()->detach();

            return true;
        } catch (\Exception $e) {

            return false;
        }
    }
}
