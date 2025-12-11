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
        'stream_link',
        'host_name',
        'prize_amount',
        'currency',
        'created_by'

    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'prize_amount' => 'float',
    ];

    protected $appends = ['stream_id'];



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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_live_shows')
            ->using(UserLiveShow::class)   // tell Laravel to use pivot model
            ->withPivot(['score', 'status', 'is_online', 'is_winner']) // include extra fields
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


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'live_show_block_users')
            ->using(LiveShowBlockUser::class)
            ->withTimestamps();
    }


    public function getStreamIdAttribute()
    {
        return $this->extractYouTubeId($this->stream_link);
    }








    function extractYouTubeId(string $url): ?string
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

            $liveShowUsers  =  $liveShow->users()->get();


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
