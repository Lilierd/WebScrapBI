<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\PendingHasThroughRelationship;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SnapshotIndex extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_time',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot_time' => 'datetime',
            // 'password' => 'hashed',
        ];
    }

    /**
     *
     */
    public function snapshots(): HasMany
    {
        return $this->hasMany(MarketShareSnapshot::class);
    }

    /**
     *
     */
    public function marketShares(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: MarketShareSnapshot::class,
            through: MarketShare::class,
            firstKey: 'market_shares.id',
            secondKey: 'market_share_snapshots.id',
        );
    }
}
