<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShopifyJob
 *
 * @property int $id
 * @property string $job_type
 * @property string $job_id
 * @property string|null $payload
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ShopifyJob extends Model
{
    protected $table = 'shopify_jobs';

    protected $fillable = [
        'job_type',
        'job_id',
        'payload',
        'status',
    ];
}
