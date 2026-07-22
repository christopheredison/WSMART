<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class DampakRisikoUnit extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    protected $fillable = [
        'risiko_id',
        'dampak_risiko',
    ];

    public function risiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function perlakuanDampakRisikos()
    {
        return $this->hasMany(PerlakuanDampakRisikoUnit::class, 'dampak_risiko_id');
    }
}
