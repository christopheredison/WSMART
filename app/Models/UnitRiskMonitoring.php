<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitRiskMonitoring extends Model
{
    public const STATUS_DRAFT_REVISI = 1;
    public const STATUS_VERIFIKASI_ROW_DIVISI = 2;       // Menunggu Level 2 (Owner Divisi)
    public const STATUS_VERIFIKASI_RO_DIVISI_MR = 3;     // Menunggu Level 1 (Officer MR)
    public const STATUS_VERIFIKASI_ROW_DIVISI_MR = 4;    // Menunggu Level 2 (Owner MR)
    public const STATUS_PUBLISHED = 100;                 // Selesai

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
        'aktual_current',
        'aktual_month_1',
        'aktual_month_2',
        'aktual_status',
        'efektivitas_perlakuan_risiko',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_revision' => 'boolean',
        'efektivitas_perlakuan_risiko' => 'decimal:2',
    ];

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'identifikasi_risiko_id');
    }

    public function getTahunPeriodeAttribute()
    {
        return $this->identifikasiRisiko->periode->tahun ?? null;
    }

    public function perlakuanPenyebabRisikos()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'penyebab_risiko_id', 'identifikasi_risiko_id');
    }

    public function perlakuanPenyebabMonitorings()
    {
        return $this->hasMany(PerlakuanPenyebabUnitMonitoring::class);
    }

    public function perlakuanDampakMonitorings()
    {
        return $this->hasMany(PerlakuanDampakMonitoringUnit::class, 'monitoring_id');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function kriUnitMonitorings()
    {
        return $this->hasMany(KRIUnitMonitoring::class, 'unit_risk_monitoring_id');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_id');
    }

    public function skalaDampakObj()
    {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak');
    }

    public function perlakuanPenyebabRisikoDocuments()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnitDocument::class, 'unit_risk_monitoring_id', 'id');
    }

    public function pengendalians()
    {
        return $this->hasMany(UnitRiskPengendalian::class, 'monitoring_id');
    }

    /**
     * Definisi Workflow untuk Monitoring Divisi
     */
    public static function getWorkflow()
    {
        return [
            1 => ['level' => 1, 'label' => 'Risk Officer Divisi', 'unit_mr' => false], // Input
            2 => ['level' => 2, 'label' => 'Risk Owner Divisi', 'unit_mr' => false],   // Verifikasi 1
            3 => ['level' => 1, 'label' => 'Risk Officer MR', 'permission' => 'verification_mr', 'unit_mr' => true], // Verifikasi 2
            4 => ['level' => 2, 'label' => 'Risk Owner MR', 'permission' => 'verification_mr', 'unit_mr' => true],   // Verifikasi Final
        ];
    }

    /**
     * Logic Pengembalian Status (Rejection)
     */
    public static function getReturnStatus($currentStatus, $isUnitMr = false)
    {
        if ($isUnitMr) {
            return 1; // Kembali ke Draft (Officer Divisi MR)
        }

        return match ((int)$currentStatus) {
            2 => 1, // Risk Owner Divisi Reject -> Balik ke Officer Divisi
            3 => 1, // Risk Officer MR Reject -> Balik ke Officer  Divisi
            4 => 1, // Risk Owner MR Reject -> Balik ke Officer Divisi
            default => 1,
        };
    }
}
