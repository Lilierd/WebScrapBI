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

    public function getCodeAttribute() : string
    {
        return substr($this->isin, 0, 12);
    }


}
