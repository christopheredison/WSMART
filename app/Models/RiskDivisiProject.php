<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskDivisiProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'identifikasi_risiko_id', // ID risiko divisi
        'project_risk_id',        // ID risiko proyek
    ];

    /**
     * Relasi ke model IdentifikasiRisiko (risiko divisi)
     */
    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'identifikasi_risiko_id');
    }

    /**
     * Relasi ke model ProjectRisk (risiko proyek)
     */
    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class, 'project_risk_id');
    }
}
