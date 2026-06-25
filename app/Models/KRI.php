<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class KRI extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $auditExclude = [
        'nilai_kri_terkini_q1',
        'nilai_kri_terkini_q2',
        'nilai_kri_terkini_q3',
        'nilai_kri_terkini_q4',
        'status_kri_terkini_q1',
        'status_kri_terkini_q2',
        'status_kri_terkini_q3',
        'status_kri_terkini_q4',
    ];

    protected $guarded = [];
    protected $table = 'key_risk_indicators';

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function getStatusKriAttribute()
    {
        return $this->status_kri_terkini_q4 ?: $this->status_kri_terkini_q3 ?: $this->status_kri_terkini_q2 ?: $this->status_kri_terkini_q1;
    }

    public function kriUnitMonitorings()
    {
        return $this->hasMany(KRIUnitMonitoring::class, 'key_risk_indicator_id', 'id');
    }

    public function unitRiskPengendalians()
    {
        return $this->hasMany(UnitRiskPengendalian::class, 'kri_id', 'id');
    }
}
