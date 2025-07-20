<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'risiko_id',
        'quarter',
        'tahun',
        'nilai_dampak',
        'skala_dampak',
        'nilai_probabilitas',
        'skala_probabilitas_id',
        'skala_risiko',
        'level_risiko',
        'eksposure_risiko',
        'month',
    ];

    protected $casts = [
        'nilai_dampak' => 'decimal:2',
        'eksposure_risiko' => 'decimal:2',
    ];

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class, 'risiko_id');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_id');
    }

    public function perlakuanPenyebabMonitorings()
    {
        return $this->hasMany(PerlakuanPenyebabMonitoring::class, 'project_monitoring_id');
    }

    public function perlakuanPenyebabRisikoDocuments()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoDocument::class, 'project_monitoring_id');
    }

    public function kriProyekMonitorings()
    {
        return $this->hasMany(KRIProjectMonitoring::class, 'project_monitoring_id');
    }

    public function documents() {
        return $this->hasMany(ProjectRiskMonitoringDocument::class);
    }

    public function skalaDampakObj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak');
    }
}
