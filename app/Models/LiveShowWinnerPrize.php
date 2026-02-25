<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveShowWinnerPrize extends Model
{
    protected $table = 'live_show_winner_prizes';

    protected $fillable = ['live_show_id', 'rank', 'prize'];

    protected $casts = [
        'prize' => 'string',
        'rank' => 'integer',
    ];

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }
}
