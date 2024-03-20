<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketShareSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'volume',
        'last_value',
        'open_value',
        'close_value',
        'high_value',
        'low_value',
        'snapshot_index_id',
        'market_share_id',
    ];
}
