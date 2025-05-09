<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $fillable = [
        'type',
        'key',
        'data',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
