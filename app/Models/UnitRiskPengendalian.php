<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitRiskPengendalian extends Model
{
    protected $guarded = [];

    public function monitoring()
    {
        return $this->belongsTo(UnitRiskMonitoring::class, 'monitoring_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ParameterRisikoUnit::class, 'parameter_id');
    }

    public function kri()
    {
        return $this->belongsTo(KRI::class, 'kri_id', 'id');
    }
}
