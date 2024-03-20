<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnapshotIndex extends Model
{
    use HasFactory;

    protected $fillable = [
        'snapshot_time',
    ];
}
