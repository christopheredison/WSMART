<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class DampakRisikoProject extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'risiko_id',
        'dampak_risiko',
    ];

    public function risiko()
    {
        return $this->belongsTo(ProjectRisk::class, 'risiko_id');
    }

    public function perlakuanDampakRisikos()
    {
        return $this->hasMany(PerlakuanDampakRisiko::class, 'dampak_risiko_id');
    }

    public function monitorings()
    {
        return $this->hasManyThrough(
            PerlakuanDampakMonitoring::class,
            PerlakuanDampakRisiko::class,
            'dampak_risiko_id',
            'perlakuan_dampak_id'
        );
    }
}
