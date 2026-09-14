<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class PenyebabRisikoProject extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    protected $auditExclude = [
        'progress_rencana_perlakuan_risiko_q1',
        'progress_rencana_perlakuan_risiko_q2',
        'progress_rencana_perlakuan_risiko_q3',
        'progress_rencana_perlakuan_risiko_q4',
        'realisasi_biaya_perlakuan_risiko_q1',
        'realisasi_biaya_perlakuan_risiko_q2',
        'realisasi_biaya_perlakuan_risiko_q3',
        'realisasi_biaya_perlakuan_risiko_q4',
    ];

    protected $fillable = [
        'risiko_id',
        'penyebab_risiko',
        'rencana_perlakuan_risiko',
        'output_perlakuan_risiko',
        'biaya_perlakuan_risiko',
        'pic',
        'timeline_perlakuan_risiko',
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

    public $casts = [
        'biaya_perlakuan_risiko' => 'float',
        'progress_rencana_perlakuan_risiko_q1' => 'float',
        'progress_rencana_perlakuan_risiko_q2' => 'float',
        'progress_rencana_perlakuan_risiko_q3' => 'float',
        'progress_rencana_perlakuan_risiko_q4' => 'float',
        'realisasi_biaya_perlakuan_risiko_q1' => 'float',
        'realisasi_biaya_perlakuan_risiko_q2' => 'float',
        'realisasi_biaya_perlakuan_risiko_q3' => 'float',
        'realisasi_biaya_perlakuan_risiko_q4' => 'float',
    ];

    public function risiko()
    {
        return $this->belongsTo(ProjectRisk::class, 'risiko_id');
    }

    public function opsiPerlakuanRisiko()
    {
        return $this->belongsTo(OpsiPerlakuanRisiko::class, 'opsi_perlakuan_risiko');
    }

    public function jenisRencanaPerlakuanRisiko()
    {
        return $this->belongsTo(JenisRencanaPerlakuanRisiko::class, 'jenis_rencana_perlakuan_risiko');
    }

    public function getTimelinePerlakuanRisikoAttribute($value)
    {
        if (!is_array($value)) {
            return explode(',', $value);
        }

        return $value;
    }

    public function setTimelinePerlakuanRisikoAttribute($value)
    {
        $timelines = $value;
        if (is_array($value)) {
            $timelines = implode(',', $value);
        }
        $this->attributes['timeline_perlakuan_risiko'] = $timelines;
    }

    public function perlakuanPenyebabRisiko()
    {
        return $this->hasMany(PerlakuanPenyebabRisiko::class, 'penyebab_risiko_id');
    }

    public function monitorings()
    {
        return $this->hasManyThrough(
            PerlakuanPenyebabMonitoring::class,
            PerlakuanPenyebabRisiko::class,
            'penyebab_risiko_id',
            'perlakuan_penyebab_id'
        );
    }
}
