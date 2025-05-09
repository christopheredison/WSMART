<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerlakuanPenyebabMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'perlakuan_penyebab_id',
        'project_monitoring_id',
        'progress_rencana_perlakuan_risiko',
        'realisasi_biaya_perlakuan_risiko',
        'deskripsi_perlakuan_risiko',
        'jenis_program_rkap_id',
        'jenis_program_rkap',
        'timeline_perlakuan_risiko_start',
        'timeline_perlakuan_risiko_end',
    ];

    protected $casts = [
        'progress_rencana_perlakuan_risiko' => 'decimal:2',
        'realisasi_biaya_perlakuan_risiko' => 'decimal:2',
        'timeline_perlakuan_risiko_start' => 'date',
        'timeline_perlakuan_risiko_end' => 'date',
    ];

    public function perlakuanPenyebab()
    {
        return $this->belongsTo(PerlakuanPenyebabMonitoring::class, 'perlakuan_penyebab_id');
    }

    public function projectMonitoring()
    {
        return $this->belongsTo(ProjectRiskMonitoring::class, 'project_monitoring_id');
    }

    public function jenisProgramDalamRKAP()
    {
        return $this->belongsTo(JenisProgramDalamRKAP::class, 'jenis_program_rkap_id');
    }

    public function getWaktuPerlakuanRisikoAttribute()
    {
        if (!$this->timeline_perlakuan_risiko_start) {
            return '-';
        }

        $start = $this->timeline_perlakuan_risiko_start->format('d M Y');

        return $start;
    }
}
