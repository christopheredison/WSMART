<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskLimitPeriode extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'risk_limit_periodes';

    protected $fillable = [
        'periode_id',
        'unit_id',
        'risk_limit'
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
} 