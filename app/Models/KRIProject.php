<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KRIProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'risiko_id',//berelasi dengan model ProjectRisk (table project_risks)
        'kri_id', //berelasi dengan model MasterKRI
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

        return $this->kriProjectMonitorings
            ->filter(function ($item) use ($quarter) {
                // Antisipasi jika relasi tidak diload
                if (!$item->relationLoaded('projectMonitoring')) {
                    $item->load('projectMonitoring');
                }

                return optional($item->projectMonitoring)->quarter == $quarter;
            })
            ->sortByDesc('id')
            ->first();
    }
}
