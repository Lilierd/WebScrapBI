<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Représente un message d'une action
 * (ou une réponse à un message d'une action)
 */
class ForumMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'forum_message_id', // parent is nullable
        'market_share_id',
        'title',
        'author',
        'boursorama_date',
        'content',
        // created_at
        // updated_at
        // deleted_at
    ];

    // protected $casts = [
    //     'boursorama_date' => 'date',
    // ];

    /**
     * foreign forum_message_id
     */
    public function parentMessage() : HasOne
    {
        return $this->hasOne(
            static::class,
            "forum_message_id",
            "id"
        );
    }

    /**
     * foreign market_share_id
     */
    public function marketShare() : HasOne
    {
        return $this->hasOne(
            MarketShare::class,
        );
    }
}
