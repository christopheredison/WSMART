<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAnalysis extends Model
{
    use HasFactory;

    //protected $guarded = [];
    protected $table = 'risk_analyses';

    //protected $guarded = [];
    protected $fillable = [
        // Existing fields
        'risiko_id',
        'skala_probabilitas_id',
        'area_dampak',
        'kategori_dampak',
        'deskripsi_dampak',
        'deskripsi_dampak_residual',
        'asumsi_perhitungan_dampak',
        'asumsi_perhitungan_dampak_residual',
        'eksposur_risiko',
        'risk_limit',
        'asumsi_nilai_dampak',
        'skala_probabilitas_residual_id',
        'nilai_dampak_residual',
        'skala_dampak_residual',
        'nilai_probabilitas_residual',
        'skala_risiko_residual',
        'level_risiko_residual',
        'eksposur_risiko_residual',
        'nilai_dampak',
        'skala_dampak',
        'nilai_probabilitas',
        'skala_risiko',
        'level_risiko',

        // Quarterly residual fields Q1–Q4
        // Q1
        'skala_probabilitas_residual_id_q1',
        'nilai_dampak_residual_q1',
        'skala_dampak_residual_q1',
        'nilai_probabilitas_residual_q1',
        'skala_risiko_residual_q1',
        'level_risiko_residual_q1',
        'eksposur_risiko_residual_q1',
        'deskripsi_dampak_residual_q1',
        'asumsi_perhitungan_dampak_residual_q1',
        // Q2
        'skala_probabilitas_residual_id_q2',
        'nilai_dampak_residual_q2',
        'skala_dampak_residual_q2',
        'nilai_probabilitas_residual_q2',
        'skala_risiko_residual_q2',
        'level_risiko_residual_q2',
        'eksposur_risiko_residual_q2',
        'deskripsi_dampak_residual_q2',
        'asumsi_perhitungan_dampak_residual_q2',
        // Q3
        'skala_probabilitas_residual_id_q3',
        'nilai_dampak_residual_q3',
        'skala_dampak_residual_q3',
        'nilai_probabilitas_residual_q3',
        'skala_risiko_residual_q3',
        'level_risiko_residual_q3',
        'eksposur_risiko_residual_q3',
        'deskripsi_dampak_residual_q3',
        'asumsi_perhitungan_dampak_residual_q3',
        // Q4
        'skala_probabilitas_residual_id_q4',
        'nilai_dampak_residual_q4',
        'skala_dampak_residual_q4',
        'nilai_probabilitas_residual_q4',
        'skala_risiko_residual_q4',
        'level_risiko_residual_q4',
        'eksposur_risiko_residual_q4',
        'deskripsi_dampak_residual_q4',
        'asumsi_perhitungan_dampak_residual_q4',
    ];
    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_id');
    }
    public function skalaProbabilitasResidualQ1()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_residual_id_q1');
    }

    public function skalaProbabilitasResidualQ2()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_residual_id_q2');
    }

    public function skalaProbabilitasResidualQ3()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_residual_id_q3');
    }

    public function skalaProbabilitasResidualQ4()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_residual_id_q4');
    }

    public function areaDampakObj()
    {
        return $this->belongsTo(AreaDampak::class, 'area_dampak');
    }

    // Tambahkan relasi skalaDampakObj
    public function skalaDampakObj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak', 'tingkat');
    }

    public function skalaDampakResidualQ1Obj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak_residual_q1', 'tingkat');
    }

    public function skalaDampakResidualQ2Obj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak_residual_q2', 'tingkat');
    }

    public function skalaDampakResidualQ3Obj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak_residual_q3', 'tingkat');
    }

    public function skalaDampakResidualQ4Obj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak_residual_q4', 'tingkat');
    }
}
