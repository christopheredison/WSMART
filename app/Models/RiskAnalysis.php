<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAnalysis extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'risk_analyses';

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_id');
    }

    public function areaDampakObj()
    {
        return $this->belongsTo(AreaDampak::class, 'area_dampak');
    }
}
