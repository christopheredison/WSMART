<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class DampakRisikoProject extends Model implements AuditableContract
{
    use Auditable;

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
}
