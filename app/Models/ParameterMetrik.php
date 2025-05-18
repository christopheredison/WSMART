<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterMetrik extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parameter_metriks';

    protected $fillable = [
        'metrik_strategi_risiko_id',
        'parameter',
        'satuan_ukuran',
        'nilai_batasan'
    ];

    public function metrikStrategiRisiko()
    {
        return $this->belongsTo(MetrikStrategiRisiko::class);
    }
}
