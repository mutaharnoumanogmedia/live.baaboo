<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LiveShowMessages extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_show_id',
        'user_id',
        'message',
        'is_removed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }
    // casting timestamp in created_at to a more readable format
 



    // Accessor for human-readable created_at
    public function getTimeAgoAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
