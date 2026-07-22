<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskMonitoring extends Model
{
    use HasFactory;

    public const STATUS_DRAFT_REVISI = 1;
    public const STATUS_VERIFIKASI_RO_PROJECT = 2;   // Menunggu Level 7
    public const STATUS_VERIFIKASI_RO_DIVISI = 3;    // Menunggu Level 1 (Officer Divisi)
    public const STATUS_VERIFIKASI_RO_DIVISI_MR = 4; // Menunggu Level 1 (Officer MR)
    public const STATUS_VERIFIKASI_ROW_DIVISI_MR = 5;// Menunggu Level 2 (Owner MR)
    public const STATUS_PUBLISHED = 100; // 6 (Legacy Published)

    protected $fillable = [
        'risiko_id',
        'quarter',
        'tahun',
        'nilai_dampak',
        'skala_dampak',
        'nilai_probabilitas',
        'skala_probabilitas_id',
        'skala_parameter_id',
        'skala_risiko',
        'level_risiko',
        'eksposure_risiko',
        'month',
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
        'nilai_dampak' => 'decimal:2',
        'eksposure_risiko' => 'decimal:2',
        'is_approved' => 'boolean',
        'is_revision' => 'boolean',
        'efektivitas_perlakuan_risiko' => 'decimal:2',
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

    public function perlakuanDampakMonitorings()
    {
        return $this->hasMany(PerlakuanDampakMonitoring::class, 'project_monitoring_id');
    }

    public function perlakuanPenyebabRisikoDocuments()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoDocument::class, 'project_monitoring_id');
    }

    public function perlakuanDampakRisikoDocuments()
    {
        return $this->hasMany(PerlakuanDampakRisikoDocument::class, 'project_monitoring_id');
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

    public function notes()
    {
        return $this->hasMany(RiskMonitoringNote::class, 'risiko_id', 'risiko_id')
                    ->where('type', 2)
                    ->whereColumn('quarter', 'project_risk_monitorings.quarter')
                    ->whereColumn('month', 'project_risk_monitorings.month')
                    ->whereColumn('year', 'project_risk_monitorings.tahun');
    }

    public function skalaParameter()
    {
        return $this->belongsTo(SkalaParameter::class, 'skala_parameter_id');
    }

    public function pengendalians()
    {
        return $this->hasMany(ProjectRiskPengendalian::class, 'monitoring_id');
    }

    public static function getWorkflow()
    {
        return [
            1 => ['level' => 6, 'label' => 'Risk Officer Project'], // Input
            2 => ['level' => 7, 'label' => 'Risk Owner Project'],   // Verifikasi 1
            3 => ['level' => 1, 'label' => 'Risk Officer Divisi', 'unit_mr' => false], // Verifikasi 2
            4 => ['level' => 1, 'label' => 'Risk Officer MR', 'permission' => 'verification_mr', 'unit_mr' => true], // Verifikasi 3
            5 => ['level' => 2, 'label' => 'Risk Owner MR', 'permission' => 'verification_mr', 'unit_mr' => true,], // Verifikasi Final
        ];
    }

    public static function getReturnStatus($currentStatus)
    {
        return match ((int)$currentStatus) {
            2, 3 => 1, // Step 2 (Owner Proyek) & 3 (Officer Divisi) kembali ke Step 1 (Officer Proyek)
            4    => 3, // Step 4 (Officer MR) kembali ke Step 3 (Officer Divisi)
            5    => 4, // Step 5 (Owner MR) kembali ke Step 4 (Officer MR)
            default => 1,
        };
    }
}
