<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialGift extends Model
{
    use HasFactory;

    protected $table = 'special_gifts';

    protected $fillable = [
        'live_show_id',
        'rank',
        'name',
        'type',
        'value',
        'voucher_amount',
        'discount_rule_id',
    ];

    protected $casts = [
        'rank' => 'integer',
        'value' => 'decimal:2',
        'voucher_amount' => 'decimal:2',
        'discount_rule_id' => 'integer',
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
