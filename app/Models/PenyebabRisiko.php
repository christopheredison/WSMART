<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class PenyebabRisiko extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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

    protected $guarded = [];
    protected $table = 'penyebab_risikos';

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function perlakuanPenyebabRisiko()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'penyebab_risiko_id');
    }

    public function perlakuanPenyebabRisikoUnit()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'penyebab_risiko_id');
    }
}
