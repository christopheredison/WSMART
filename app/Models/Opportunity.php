<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    protected $table = 'opportunities';

    protected $fillable = [
        'identifikasi_risiko_id',
        'project_risk_id',
        'penjelasan_peluang_rencana',
        'penjelasan_peluang_realisasi',
        'nilai_peluang_rencana',
        'nilai_peluang_realisasi',
        'file_path',
    ];

    /**
     * Get the identifikasi risiko that owns the opportunity.
     */
    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'identifikasi_risiko_id');
    }

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class, 'project_risk_id');
    }
}
