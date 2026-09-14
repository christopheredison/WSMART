<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KRIUnitMonitoring extends Model
{
    protected $fillable = [
        'key_risk_indicator_id',
        'unit_risk_monitoring_id',
        'status_kri_terkini',
        'nilai_kri_terkini',
    ];

    public function keyRiskIndicator()
    {
        return $this->belongsTo(KRI::class, 'key_risk_indicator_id', 'id')->withTrashed();
    }

    public function unitRiskMonitoring()
    {
        return $this->belongsTo(UnitRiskMonitoring::class, 'unit_risk_monitoring_id', 'id');
    }
}
