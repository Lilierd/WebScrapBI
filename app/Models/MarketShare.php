<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'isin',
        'url'
    ];

    /**
     * Créer dynamiquement une propriété sur les objets marketShare,
     * accessible depuis :
     *
     * $marketShare->code
     *
     * @return string
     */
    public function getCodeAttribute() : string
    {
        return substr($this->isin, 0, 12);
    }


}
