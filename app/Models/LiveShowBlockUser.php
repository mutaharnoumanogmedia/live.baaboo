<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LiveShowBlockUser extends Pivot
{
    use HasFactory;

    protected $table = 'live_show_block_users';
    protected $fillable = [
        'live_show_id',
        'user_id',
    ];
    public $timestamps = true;
    

}
