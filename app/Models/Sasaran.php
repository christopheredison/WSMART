<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sasaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'periode_id',
        'metrik_strategi_risiko_id',
        'sasaran',
        'expected_result',
        'risk_value',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function metrikStrategiRisiko()
    {
        return $this->belongsTo(MetrikStrategiRisiko::class, 'metrik_strategi_risiko_id');
    }

    public function strategiBisnis()
    {
        return $this->hasMany(StrategiBisnis::class, 'sasaran_id');
    }
}
