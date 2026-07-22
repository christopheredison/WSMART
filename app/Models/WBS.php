<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WBS extends Model
{
    use SoftDeletes;

    protected $table = 'w_b_s';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];
}
