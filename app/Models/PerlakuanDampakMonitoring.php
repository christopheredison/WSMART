<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerlakuanDampakMonitoring extends Model
{
    protected $fillable = [
        'perlakuan_dampak_id', 'project_monitoring_id', 'progress_rencana_perlakuan_risiko',
        'realisasi_biaya_perlakuan_risiko', 'deskripsi_perlakuan_risiko',
        'timeline_perlakuan_risiko_start', 'timeline_perlakuan_risiko_end'
    ];

    protected $casts = [
        'timeline_perlakuan_risiko_start' => 'date',
        'timeline_perlakuan_risiko_end' => 'date',
    ];

    public function perlakuanDampak() {
        return $this->belongsTo(PerlakuanDampakRisiko::class, 'perlakuan_dampak_id');
    }

    public function projectMonitoring() {
        return $this->belongsTo(ProjectRiskMonitoring::class, 'project_monitoring_id');
    }
}
