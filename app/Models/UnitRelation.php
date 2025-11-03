<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitRelation extends Model
{
    use HasFactory;

    protected $table = 'unit_relations';

    protected $fillable = [
        'unit_id',
        'related_unit_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function relatedUnit()
    {
        return $this->belongsTo(Unit::class, 'related_unit_id');
    }
}