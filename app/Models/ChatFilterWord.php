<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// chat_filter_module: a single blocked/watched term (literal, phrase or regex).
class ChatFilterWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_filter_tier_id',
        'term',
        'match_type',
        'whole_word',
        'is_active',
        'action_override',
        'note',
    ];

    protected $casts = [
        'whole_word' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tier()
    {
        return $this->belongsTo(ChatFilterTier::class, 'chat_filter_tier_id');
    }

    // chat_filter_module: the effective action for this word (override wins over tier default).
    public function effectiveAction(): string
    {
        return $this->action_override ?: ($this->tier->action ?? 'watchlist');
    }
}
