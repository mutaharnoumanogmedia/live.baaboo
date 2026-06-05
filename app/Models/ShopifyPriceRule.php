<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShopifyPriceRule
 *
 * @property int $id
 * @property string $shopify_id
 * @property string $title
 * @property string $type
 * @property float $value
 * @property int|null $usage_limit
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property bool $active
 * @property string|null $conditions
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class ShopifyPriceRule extends Model
{
	protected $table = 'shopify_price_rule';

	protected $casts = [
		'value' => 'float',
		'usage_limit' => 'int',
		'starts_at' => 'datetime',
		'ends_at' => 'datetime',
		'active' => 'bool'
	];

	protected $fillable = [
		'shopify_id',
		'title',
		'type',
		'value',
		'usage_limit',
		'starts_at',
		'ends_at',
		'active',
		'conditions'
	];

    public function getIsRunningAttribute(){
        $now = now()->setTimezone('UTC');
        return $this->active
            && (is_null($this->starts_at) || $this->starts_at->equalTo($this->starts_at->copy()->setTimezone('UTC')) && $this->starts_at <= $now)
            && (is_null($this->ends_at) || $this->ends_at->equalTo($this->ends_at->copy()->setTimezone('UTC')) && $this->ends_at >= $now);
    }

    // public function discountCodes()
    // {
    //     return $this->hasMany(ShopifyDiscountCode::class, 'price_rule_id', 'id');
    // }
}
