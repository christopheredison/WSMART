<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskPengendalian extends Model {
    protected $guarded = [];

    public function monitoring()
    {
        return $this->belongsTo(ProjectRiskMonitoring::class, 'monitoring_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ParameterRisikoProject::class, 'parameter_id');
    }
}
