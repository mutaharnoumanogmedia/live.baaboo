<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveShowWinnerPrize extends Model
{
    protected $table = 'live_show_winner_prizes';

    protected $fillable = ['live_show_id', 'rank', 'prize', 'is_voucher', 'voucher_amount', 'discount_rule_id'];

    protected $casts = [
        'prize' => 'string',
        'rank' => 'integer',
        'is_voucher' => 'boolean',
        'voucher_amount' => 'decimal:2',
    ];

    public function liveShow()
    {
        return $this->belongsTo(LiveShow::class);
    }

    public function discountRule()
    {
        return $this->belongsTo(ShopifyPriceRule::class, 'discount_rule_id');
    }
}
