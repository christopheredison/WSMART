<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitRiskMonitoring extends Model
{
    protected $fillable = [
        'identifikasi_risiko_id',
        'quarter',
        'nilai_dampak',
        'skala_dampak',
        'nilai_probabilitas',
        'skala_probabilitas_id',
        'skala_risiko',
        'level_risiko',
        'eksposure_risiko',
    ];

    public function perlakuanPenyebabRisikos()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'penyebab_risiko_id', 'identifikasi_risiko_id');
    }

    public function perlakuanPenyebabMonitorings()
    {
        return $this->hasMany(PerlakuanPenyebabUnitMonitoring::class);
    }

    public function kriUnitMonitorings()
    {
        return $this->hasMany(KRIUnitMonitoring::class, 'unit_risk_monitoring_id');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_id');
    }

    public function perlakuanPenyebabRisikoDocuments()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnitDocument::class, 'unit_risk_monitoring_id', 'id');
    }
}
