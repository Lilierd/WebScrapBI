<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function snapshotIndex() : BelongsTo
    {
        return $this->belongsTo(SnapshotIndex::class);
    }

    public function marketShare() : BelongsTo
    {
        return $this->belongsTo(MarketShare::class);
    }
}
