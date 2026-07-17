<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// chat_filter_module: severity tier (1-4) plus its default enforcement action.
class ChatFilterTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tier_number',
        'name',
        'slug',
        'description',
        'action',
        'delete_message',
        'is_enabled',
        'timeout_minutes',
        'timeout_after_offenses',
    ];

    protected $casts = [
        'tier_number' => 'integer',
        'delete_message' => 'boolean',
        'is_enabled' => 'boolean',
        'timeout_minutes' => 'integer',
        'timeout_after_offenses' => 'integer',
    ];

    public function words()
    {
        return $this->hasMany(ChatFilterWord::class);
    }

    // chat_filter_module: only active words of an enabled tier ever reach the matcher.
    public function activeWords()
    {
        return $this->hasMany(ChatFilterWord::class)->where('is_active', true);
    }
}
