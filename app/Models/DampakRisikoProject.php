<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DampakRisikoProject extends Model
{
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
