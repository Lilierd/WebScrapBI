<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function marketShare() : HasMany
    {
        return $this->hasMany(MarketShareSnapshot::class);
    }

}
