<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerlakuanPenyebabRisikoUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'penyebab_risiko_id',
        'rencana_perlakuan_risiko',
        'output_perlakuan_risiko',
        'biaya_perlakuan_risiko',
        'pic',
        'pic_jabatan_id',
        'timeline_perlakuan_risiko_start',
        'timeline_perlakuan_risiko_end',
        'opsi_perlakuan_risiko',
        'jenis_rencana_perlakuan_risiko',
        'progress_rencana_perlakuan_risiko_q1',
        'progress_rencana_perlakuan_risiko_q2',
        'progress_rencana_perlakuan_risiko_q3',
        'progress_rencana_perlakuan_risiko_q4',
        'realisasi_biaya_perlakuan_risiko_q1',
        'realisasi_biaya_perlakuan_risiko_q2',
        'realisasi_biaya_perlakuan_risiko_q3',
        'realisasi_biaya_perlakuan_risiko_q4',

    ];

    protected $casts = [
        'timeline_perlakuan_risiko_start' => 'date',
        'timeline_perlakuan_risiko_end' => 'date',
    ];

    public function penyebabRisiko()
    {
        return $this->belongsTo(PenyebabRisiko::class, 'penyebab_risiko_id');
    }

    public function documents()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnitDocument::class, 'perlakuan_penyebab_risiko_unit_id', 'id');
    }

    public function monitorings()
    {
        return $this->hasMany(PerlakuanPenyebabUnitMonitoring::class, 'perlakuan_penyebab_risiko_unit_id', 'id');
    }

    public function lastMonitoring()
    {
        return $this->hasOne(PerlakuanPenyebabUnitMonitoring::class, 'perlakuan_penyebab_risiko_unit_id')->orderBy('created_at', 'desc');
    }

    public function getTimelinePerlakuanRisikoAttribute()
    {
        if (!$this->lastMonitoring) {
            return null;
        }
        return [
            $this->lastMonitoring->timeline_perlakuan_risiko_start?->format('d/m/Y'),
            $this->lastMonitoring->timeline_perlakuan_risiko_end?->format('d/m/Y'),
        ];
    }

    public function getDeskripsiPerlakuanRisikoAttribute()
    {
        return $this->lastMonitoring?->deskripsi_perlakuan_risiko;
    }

    public function getJenisProgramRkapAttribute()
    {
        return $this->lastMonitoring?->jenis_program_rkap;
    }

    public function getJenisProgramRkapIdAttribute()
    {
        return $this->lastMonitoring?->jenis_program_rkap_id;
    }

    public function getWaktuPerlakuanRisikoAttribute()
    {
        if (!$this->timeline_perlakuan_risiko_start) {
            return '-';
        }

        $start = $this->timeline_perlakuan_risiko_start->format('d M Y');
        
        // Jika tanggal start dan end sama, tampilkan hanya satu tanggal
        if ($this->timeline_perlakuan_risiko_end && 
            $this->timeline_perlakuan_risiko_start->format('Y-m-d') === $this->timeline_perlakuan_risiko_end->format('Y-m-d')) {
            return $start;
        }

        // Jika ada end date, tampilkan range
        if ($this->timeline_perlakuan_risiko_end) {
            return $start . ' - ' . $this->timeline_perlakuan_risiko_end->format('d M Y');
        }

        return $start;
    }

    public function perlakuanPenyebabMonitorings()
    {
        return $this->hasMany(PerlakuanPenyebabUnitMonitoring::class, 'perlakuan_penyebab_risiko_unit_id', 'id');
    }

    public function perlakuanPenyebabUnitMonitorings()
    {
        return $this->hasMany(PerlakuanPenyebabUnitMonitoring::class, 'perlakuan_penyebab_risiko_unit_id', 'id');
    }
    
    public function picJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'pic_jabatan_id');
    }
}
