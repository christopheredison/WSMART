<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaDampakDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function areaDampak()
    {
        return $this->belongsTo(AreaDampak::class, 'area_dampak_id');
    }
}
