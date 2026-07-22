<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskMap extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'risk_maps';

    protected $fillable = [
        'level_risiko',
        'nilai_risiko',
        'skala_dampak',
        'skala_probabilitas',
    ];
}
