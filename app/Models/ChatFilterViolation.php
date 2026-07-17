<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// chat_filter_module: one logged filter hit (ban log + watchlist source of truth).
class ChatFilterViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'live_show_id',
        'chat_filter_word_id',
        'tier_number',
        'matched_term',
        'original_message',
        'action_taken',
        'is_reviewed',
    ];

    protected $casts = [
        'tier_number' => 'integer',
        'is_reviewed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function word()
    {
        return $this->belongsTo(ChatFilterWord::class, 'chat_filter_word_id');
    }

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class, 'live_show_id');
    }
}
