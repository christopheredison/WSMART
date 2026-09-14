<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class KRIProject extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

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

    protected $fillable = [
        'risiko_id',
        'kri_id',
        'kri',
        'satuan_kri',
        'batas_aman',
        'batas_waspada',
        'batas_bahaya',
        'status_kri_terkini_q1',
        'nilai_kri_terkini_q1',
        'status_kri_terkini_q2',
        'nilai_kri_terkini_q2',
        'status_kri_terkini_q3',
        'nilai_kri_terkini_q3',
        'status_kri_terkini_q4',
        'nilai_kri_terkini_q4',
    ];

    public function risiko()
    {
        return $this->belongsTo(ProjectRisk::class, 'risiko_id');
    }

    public function kri()
    {
        return $this->belongsTo(MasterKRI::class, 'kri_id');
    }

    public function kriProjectMonitorings()
    {
        return $this->hasMany(KRIProjectMonitoring::class, 'kri_project_id');
    }

    public function getLastMonitoringAttribute()
    {
        $quarter = request()->input('quarter');
        $tahun = request()->input('tahun');

        return $this->kriProjectMonitorings
            ->filter(function ($item) use ($quarter, $tahun) {
                if (!$item->relationLoaded('projectMonitoring')) {
                    $item->load('projectMonitoring');
                }

                return optional($item->projectMonitoring)->quarter == $quarter && optional($item->projectMonitoring)->tahun == $tahun;
            })
            ->sortByDesc('id')
            ->first();
    }

    public function getStatusKriTerkiniAttribute()
    {
        return $this->lastMonitoring?->status_kri_terkini;
    }

    public function getNilaiKriTerkiniAttribute()
    {
        return $this->lastMonitoring?->nilai_kri_terkini;
    }
}
