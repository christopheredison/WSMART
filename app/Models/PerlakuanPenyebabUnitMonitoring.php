<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerlakuanPenyebabUnitMonitoring extends Model
{
    protected $fillable = [
        'perlakuan_penyebab_risiko_unit_id',
        'unit_risk_monitoring_id',
        'progress_rencana_perlakuan_risiko',
        'realisasi_biaya_perlakuan_risiko',
        'deskripsi_perlakuan_risiko',
        'jenis_program_rkap',
        'jenis_program_rkap_id',
        'timeline_perlakuan_risiko_start',
        'timeline_perlakuan_risiko_end',
    ];

    protected $casts = [
        'timeline_perlakuan_risiko_start' => 'date:Y-m-d',
        'timeline_perlakuan_risiko_end' => 'date:Y-m-d',
    ];

    public function perlakuanPenyebabRisikoUnit()
    {
        return $this->belongsTo(PerlakuanPenyebabRisikoUnit::class, 'perlakuan_penyebab_risiko_unit_id', 'id');
    }

    public function unitRiskMonitoring()
    {
        return $this->belongsTo(UnitRiskMonitoring::class, 'unit_risk_monitoring_id', 'id');
    }
}
