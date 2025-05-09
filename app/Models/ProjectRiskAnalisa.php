<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskAnalisa extends Model
{
    use HasFactory;

    protected $fillable = [
        'risiko_id',//berelasi dengan model ProjectRisk (table project_risks)
        'skala_probabilitas_id', //berelasi dengan model SkalaProbabilitas (table skala_probabilitas)
        'area_dampak', //berelasi dengan model AreaDampak (table area_dampaks)
        'kategori_dampak', //isian pilihan Finansial atau Non Finansial
        'deskripsi_dampak',
        'deskripsi_dampak_residual',
        'asumsi_perhitungan_dampak',
        'asumsi_perhitungan_dampak_residual',
        'nilai_dampak',
        'skala_dampak',
        'nilai_probabilitas',
        'skala_risiko',
        'level_risiko',
        'risk_limit',
        'eksposur_risiko',
        'skala_probabilitas_residual_id', //berelasi dengan model SkalaProbabilitas (table skala_probabilitas)
        'nilai_dampak_residual',
        'skala_dampak_residual',
        'nilai_probabilitas_residual',
        'skala_risiko_residual',
        'level_risiko_residual',
        'eksposur_risiko_residual',
    ];

    public const KATEGORI_DAMPAK_KUANTITATIF = 'Kuantitatif';
    public const KATEGORI_DAMPAK_KUALITATIF = 'Kualitatif';

    public $casts = [
        'nilai_dampak' => 'integer',
        'skala_dampak' => 'integer',
        'skala_risiko' => 'integer',
        'risk_limit' => 'float',
        'nilai_dampak_residual' => 'integer',
        'skala_dampak_residual' => 'integer',
        'skala_risiko_residual' => 'integer',
    ];

    public function risiko()
    {
        return $this->belongsTo(ProjectRisk::class, 'risiko_id');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class);
    }

    public function areaDampakObj()
    {
        return $this->belongsTo(AreaDampak::class, 'area_dampak');
    }

    public function skalaProbabilitasResidual()
    {
        //return $this->belongsTo(SkalaProbabilitas::class);
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_residual_id');
    }

    public function skalaDampakObj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak');
    }

    public function skalaDampakResidualObj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak_residual');
    }
}
