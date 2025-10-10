<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitRiskMonitoring extends Model
{
    public const STATUS_DRAFT_REVISI = 1;
    public const STATUS_VERIFIKASI_ROW_DIVISI = 2;       // Menunggu Level 2 (Owner Divisi)
    public const STATUS_VERIFIKASI_RO_DIVISI_MR = 3;     // Menunggu Level 1 (Officer MR)
    public const STATUS_VERIFIKASI_ROW_DIVISI_MR = 4;    // Menunggu Level 2 (Owner MR)
    public const STATUS_PUBLISHED = 5;                   // Selesai

    protected $fillable = [
        'identifikasi_risiko_id',
        'quarter',
        'month',
        'nilai_dampak',
        'skala_dampak',
        'nilai_probabilitas',
        'skala_probabilitas_id',
        'skala_risiko',
        'level_risiko',
        'eksposure_risiko',
        'status',
        'is_approved',
        'is_revision',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_revision' => 'boolean',
    ];

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'identifikasi_risiko_id');
    }

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
